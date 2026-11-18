#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
IMAGE="xcloner-build:local"

command -v docker >/dev/null || { echo "Docker is required." >&2; exit 1; }
docker info >/dev/null
docker build --tag "$IMAGE" "$PROJECT_DIR/docker/build"
docker run --rm --init \
    --user "$(id -u):$(id -g)" \
    --env HOME=/tmp \
    --env "XCLONER_BUILD_MEMORY_LIMIT=${XCLONER_BUILD_MEMORY_LIMIT:-2G}" \
    --mount "type=bind,source=$PROJECT_DIR,target=/source,readonly" \
    --mount "type=bind,source=$PROJECT_DIR,target=/output" \
    "$IMAGE"
