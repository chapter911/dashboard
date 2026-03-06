#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: ./tools/use-env.sh [development|production]"
  exit 1
fi

case "$1" in
  development)
    cp .env.development .env
    echo "Active environment: development"
    ;;
  production)
    cp .env.production .env
    echo "Active environment: production"
    ;;
  *)
    echo "Invalid option: $1"
    echo "Usage: ./tools/use-env.sh [development|production]"
    exit 1
    ;;
esac
