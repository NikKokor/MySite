#!/bin/bash

commitRegex='^(\[[0-9]+\].+)'
if ! grep -qE "$commitRegex" "$1"; then
    echo "Invalid message. Expected: [number of issue] message"
    exit 1
fi
