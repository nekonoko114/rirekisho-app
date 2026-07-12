#!/bin/bash

# Copy pre-push hook into git hooks folder
cp scripts/git-hooks/pre-push .git/hooks/pre-push

# Make it executable
chmod +x .git/hooks/pre-push

echo "====================================================="
echo "Git hook (pre-push) has been successfully configured!"
echo "====================================================="
