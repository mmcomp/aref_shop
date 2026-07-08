#!/usr/bin/env bash
set -euo pipefail

cd "$(git rev-parse --show-toplevel)"

VERSION_FILE="VERSION"

current_version=$(cat "$VERSION_FILE")

if [[ -z "$current_version" ]]; then
  echo "Could not find version in $VERSION_FILE" >&2
  exit 1
fi

IFS='.' read -r major minor patch <<< "$current_version"
new_version="${major}.${minor}.$((patch + 1))"

echo "$new_version" > "$VERSION_FILE"

echo "Bumping version: ${current_version} -> ${new_version}"

git add "$VERSION_FILE"
git commit -m "v${new_version}"
git tag "v${new_version}"
git push
git push origin "v${new_version}"
