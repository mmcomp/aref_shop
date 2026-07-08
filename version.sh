#!/usr/bin/env bash
set -euo pipefail

cd "$(git rev-parse --show-toplevel)"

MAIN_GO="abbas"


current_version=$(grep -E '^var version = "' "$MAIN_GO" | sed -E 's/^var version = "([^"]*)".*/\1/')
echo "START"


if [[ -z "$current_version" ]]; then
  echo "Could not find version in $MAIN_GO" >&2
  exit 1
fi

IFS='.' read -r major minor patch <<< "$current_version"
new_version="${major}.${minor}.$((patch + 1))"

sed -i '' -E "s/^var version = \"${current_version}\"/var version = \"${new_version}\"/" "$MAIN_GO"

echo "Bumping version: ${current_version} -> ${new_version}"

git add "$MAIN_GO"
git commit -m "v${new_version}"
git tag "v${new_version}"
git push
git push origin "v${new_version}"
