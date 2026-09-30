#!/bin/bash
set -e
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
MODX_DIR="/Users/dos/Desktop/plugins/modx"

if [ ! -f "$MODX_DIR/core/model/modx/modx.class.php" ]; then
  echo "MODX not found at $MODX_DIR"
  exit 1
fi

echo "Building Universal ePay..."
cd "$PROJECT_DIR"
php _build/build.php
echo
echo "Done. Open MODX → System → Package Management."
echo "The package should be in:"
echo "$MODX_DIR/core/packages/"
