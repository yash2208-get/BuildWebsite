#!/usr/bin/env python3
"""
gh_composer.py — a minimal Composer-compatible dependency installer that resolves
and downloads packages directly from GitHub (used because Packagist is not
reachable from this build environment).

It reads composer.json, recursively resolves `require` constraints against git
tags on GitHub, downloads zipballs, and writes a PSR-4/classmap/files autoloader
that is byte-compatible in behaviour with Composer's generated autoloader.
"""
import json, os, re, sys, io, zipfile, urllib.request, urllib.error, functools, shutil

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
VENDOR = os.path.join(ROOT, "vendor")
CACHE = os.path.join(ROOT, ".gh-composer-cache")
os.makedirs(CACHE, exist_ok=True)

TOKEN = os.environ.get("GITHUB_TOKEN") or os.environ.get("GH_TOKEN")

# packagist name -> github repo, where they differ
REPO_MAP = {
    "psr/log": "php-fig/log",
    "psr/container": "php-fig/container",
    "psr/simple-cache": "php-fig/simple-cache",
    "psr/http-message": "php-fig/http-message",
    "psr/http-factory": "php-fig/http-factory",
    "psr/http-client": "php-fig/http-client",
    "psr/event-dispatcher": "php-fig/event-dispatcher",
    "psr/cache": "php-fig/cache",
    "nesbot/carbon": "CarbonPHP/carbon",
    "monolog/monolog": "Seldaek/monolog",
    "tijsverkoyen/css-to-inline-styles": "tijsverkoyen/CssToInlineStyles",
    "dragonmantank/cron-expression": "dragonmantank/cron-expression",
    "doctrine/inflector": "doctrine/inflector",
    "doctrine/lexer": "doctrine/lexer",
    "vlucas/phpdotenv": "vlucas/phpdotenv",
    "voku/portable-ascii": "voku/portable-ascii",
    "graham-campbell/result-type": "GrahamCampbell/Result-Type",
    "phpoption/phpoption": "schmittjoh/php-option",
    "ralouphie/getallheaders": "ralouphie/getallheaders",
    "symfony/polyfill-ctype": "symfony/polyfill-ctype",
    "carbonphp/carbon-doctrine-types": "CarbonPHP/carbon-doctrine-types",
    "brick/math": "brick/math",
    "guzzlehttp/psr7": "guzzle/psr7",
    "guzzlehttp/promises": "guzzle/promises",
    "guzzlehttp/guzzle": "guzzle/guzzle",
    "guzzlehttp/uri-template": "guzzle/uri-template",
    "fruitcake/php-cors": "fruitcake/php-cors",
    "nunomaduro/termwind": "nunomaduro/termwind",
    "spatie/laravel-permission": "spatie/laravel-permission",
    "spatie/laravel-package-tools": "spatie/laravel-package-tools",
    "livewire/livewire": "livewire/livewire",
    "laravel/framework": "laravel/framework",
    "laravel/prompts": "laravel/prompts",
    "laravel/serializable-closure": "laravel/serializable-closure",
    "laravel/tinker": "laravel/tinker",
    "psy/psysh": "bobthecow/psysh",
    "nikic/php-parser": "nikic/PHP-Parser",
    "webmozart/assert": "webmozarts/assert",
    "symfony/var-exporter": "symfony/var-exporter",
    "league/mime-type-detection": "thephpleague/mime-type-detection",
    "league/flysystem": "thephpleague/flysystem",
    "league/flysystem-local": "thephpleague/flysystem-local",
    "league/commonmark": "thephpleague/commonmark",
    "league/config": "thephpleague/config",
    "league/uri": "thephpleague/uri",
    "league/uri-interfaces": "thephpleague/uri-interfaces",
    "dflydev/dot-access-data": "dflydev/dflydev-dot-access-data",
    "nette/schema": "nette/schema",
    "nette/utils": "nette/utils",
    "ramsey/uuid": "ramsey/uuid",
    "ramsey/collection": "ramsey/collection",
    "egulias/email-validator": "egulias/EmailValidator",
    "psr/clock": "php-fig/clock",
    "symfony/psr-http-message-bridge": "symfony/psr-http-message-bridge",
}

# packages that live inside a monorepo subtree: name -> (repo, subpath)
SUBTREE = {}

SKIP = {"php", "composer-runtime-api", "composer-plugin-api", "composer/installers"}


def gh(url, raw=False):
    req = urllib.request.Request(url)
    req.add_header("Accept", "application/vnd.github+json")
    req.add_header("User-Agent", "gh-composer/1.0")
    if TOKEN:
        req.add_header("Authorization", "Bearer " + TOKEN)
    for attempt in range(4):
        try:
            with urllib.request.urlopen(req, timeout=90) as r:
                data = r.read()
                return data if raw else json.loads(data)
        except urllib.error.HTTPError as e:
            if e.code in (403, 429) and attempt < 3:
                import time
                time.sleep(5 * (attempt + 1))
                continue
            raise
        except Exception:
            if attempt < 3:
                import time
                time.sleep(3)
                continue
            raise


def repo_for(name):
    if name in REPO_MAP:
        return REPO_MAP[name]
    if name in SUBTREE:
        return SUBTREE[name][0]
    return name


@functools.lru_cache(maxsize=None)
def tags(repo):
    out, page = [], 1
    while page <= 6:
        try:
            batch = gh(f"https://api.github.com/repos/{repo}/tags?per_page=100&page={page}")
        except Exception:
            break
        if not batch:
            break
        out += [t["name"] for t in batch]
        if len(batch) < 100:
            break
        page += 1
    return out


VER_RE = re.compile(r"^v?(\d+)\.(\d+)(?:\.(\d+))?(?:\.(\d+))?(?:-(alpha|beta|rc|RC|dev)\.?(\d*))?$")


def parse_ver(tag):
    m = VER_RE.match(tag.strip())
    if not m:
        return None
    maj, mi, pa, ex, pre, pren = m.groups()
    stability = 0 if pre else 1
    return (int(maj), int(mi), int(pa or 0), int(ex or 0), stability, int(pren or 0)), bool(pre)


def satisfies(v, constraint):
    """Evaluate a composer constraint string against a parsed version tuple."""
    constraint = constraint.strip()
    # Composer treats both "||" and "|" as OR separators.
    for alt in constraint.replace("||", "|").split("|"):
        alt = alt.strip()
        if not alt:
            continue
        if all(_range_ok(v, part) for part in re.split(r"[,\s]+", alt) if part):
            return True
    return False


def _bump(parts, idx):
    p = list(parts)
    p[idx] += 1
    for i in range(idx + 1, 4):
        p[i] = 0
    return tuple(p)


def _range_ok(v, part):
    part = part.strip()
    if part in ("*", ""):
        return True
    m = re.match(r"^(\^|~|>=|<=|>|<|=|!=)?\s*v?(\d+)(?:\.(\d+))?(?:\.(\d+))?(?:\.(\d+))?(?:-(?:alpha|beta|rc|RC|dev)\.?\d*)?$", part)
    if not m:
        return True  # unparseable (dev-master etc.) -> don't block
    op = m.group(1) or "="
    nums = tuple(int(x) if x else 0 for x in m.groups()[1:5])
    given = [x for x in m.groups()[1:5] if x is not None]
    prec = len(given)
    ver = v[:4]
    if op == "^":
        if nums[0] > 0:
            return ver >= nums and ver < _bump(nums, 0)
        if nums[1] > 0 or prec > 1:
            return ver >= nums and ver < _bump(nums, 1)
        return ver >= nums and ver < _bump(nums, 0)
    if op == "~":
        # ~1 and ~1.2 -> next major; ~1.2.3 -> next minor; ~1.2.3.4 -> next patch
        idx = 0 if prec <= 2 else prec - 2
        return ver >= nums and ver < _bump(nums, idx)
    if op == ">=":
        return ver >= nums
    if op == ">":
        return ver > nums
    if op == "<=":
        return ver <= nums
    if op == "<":
        return ver < nums
    if op == "!=":
        return ver[:prec] != nums[:prec]
    return ver[:prec] == nums[:prec]


def pick_version(name, constraint):
    repo = repo_for(name)
    best = None
    for t in tags(repo):
        pv = parse_ver(t)
        if not pv:
            continue
        v, is_pre = pv
        if is_pre:
            continue
        if satisfies(v, constraint):
            if best is None or v > best[0]:
                best = (v, t)
    return (repo, best[1]) if best else (repo, None)


def download(repo, tag, dest):
    key = f"{repo.replace('/', '_')}@{tag}.zip"
    path = os.path.join(CACHE, key)
    if not os.path.exists(path):
        data = gh(f"https://api.github.com/repos/{repo}/zipball/{tag}", raw=True)
        with open(path, "wb") as f:
            f.write(data)
    with zipfile.ZipFile(path) as z:
        names = z.namelist()
        root = names[0].split("/")[0] + "/"
        if os.path.isdir(dest):
            shutil.rmtree(dest)
        os.makedirs(dest, exist_ok=True)
        for n in names:
            if not n.startswith(root) or n.endswith("/"):
                continue
            rel = n[len(root):]
            if not rel:
                continue
            target = os.path.join(dest, rel)
            os.makedirs(os.path.dirname(target), exist_ok=True)
            with z.open(n) as src, open(target, "wb") as out:
                shutil.copyfileobj(src, out)


def load_cj(name, repo, tag):
    """Fetch just composer.json for a package@tag (cheap metadata read)."""
    key = os.path.join(CACHE, "meta_" + repo.replace("/", "_") + "@" + tag + ".json")
    if os.path.exists(key):
        try:
            return json.load(open(key))
        except Exception:
            pass
    try:
        raw = gh(f"https://raw.githubusercontent.com/{repo}/{tag}/composer.json", raw=True)
        cj = json.loads(raw)
    except Exception:
        try:
            blob = gh(f"https://api.github.com/repos/{repo}/contents/composer.json?ref={tag}")
            import base64
            cj = json.loads(base64.b64decode(blob["content"]))
        except Exception:
            cj = {}
    json.dump(cj, open(key, "w"))
    return cj


def main():
    root_json = json.load(open(os.path.join(ROOT, "composer.json")))
    root_req = dict(root_json.get("require", {}))
    if "--dev" in sys.argv:
        root_req.update(root_json.get("require-dev", {}))

    # ---- resolution: iterate to a fixed point honouring `replace` ----
    constraints = {}      # name -> list of constraint strings
    chosen = {}           # name -> (repo, tag, composer.json)
    replaced = set()      # names provided by another package

    for n, c in root_req.items():
        if n.lower() in SKIP or n.lower().startswith("ext-"):
            continue
        constraints[n.lower()] = [c]

    for _ in range(40):
        changed = False
        for name in sorted(list(constraints)):
            if name in replaced or name in SKIP or name.startswith("ext-"):
                continue
            cons = constraints[name]
            cur = chosen.get(name)
            if cur and all(satisfies(parse_ver(cur[1])[0], c) for c in cons):
                continue
            repo = repo_for(name)
            best = None
            for t in tags(repo):
                pv = parse_ver(t)
                if not pv or pv[1]:
                    continue
                v = pv[0]
                if all(satisfies(v, c) for c in cons):
                    if best is None or v > best[0]:
                        best = (v, t)
            if not best:
                print(f"  !! unresolvable {name} {cons}", flush=True)
                continue
            cj = load_cj(name, repo, best[1])
            chosen[name] = (repo, best[1], cj)
            changed = True
            # register replaces
            for rname in (cj.get("replace") or {}):
                rl = rname.lower()
                if rl not in replaced:
                    replaced.add(rl)
                    chosen.pop(rl, None)
                    changed = True
            for dep, dc in (cj.get("require") or {}).items():
                d = dep.lower()
                if d in SKIP or d.startswith("ext-") or d in replaced:
                    continue
                lst = constraints.setdefault(d, [])
                if dc not in lst:
                    lst.append(dc)
                    changed = True
        if not changed:
            break

    # ---- download ----
    final = {n: v for n, v in chosen.items() if n not in replaced}
    print(f"installing {len(final)} packages", flush=True)
    for name, (repo, tag, cj) in sorted(final.items()):
        dest = os.path.join(VENDOR, *name.split("/"))
        marker = os.path.join(dest, ".gh-composer-version")
        if os.path.exists(marker) and open(marker).read().strip() == tag:
            continue
        print(f"  - {name} {tag}", flush=True)
        try:
            if name in SUBTREE:
                repo_, sub = SUBTREE[name]
                tmp = os.path.join(CACHE, "tmp_" + name.replace("/", "_"))
                download(repo_, tag, tmp)
                src = os.path.join(tmp, sub)
                if os.path.isdir(dest):
                    shutil.rmtree(dest)
                shutil.copytree(src, dest)
            else:
                download(repo, tag, dest)
            open(marker, "w").write(tag)
        except Exception as e:
            print(f"  !! {name}: {e}", flush=True)

    # prune packages that ended up replaced
    for name in replaced:
        d = os.path.join(VENDOR, *name.split("/"))
        if os.path.isdir(d):
            shutil.rmtree(d)

    json.dump({k: [v[0], v[1]] for k, v in sorted(final.items())},
              open(os.path.join(ROOT, "gh-composer.lock"), "w"), indent=1)
    print(f"resolved {len(final)} packages")


if __name__ == "__main__":
    main()
