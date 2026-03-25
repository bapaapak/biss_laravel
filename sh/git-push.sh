#!/bin/bash
# Simple script to add, commit, and push all changes to git

echo "[GIT] Adding all changes..."
git add .

echo "[GIT] Committing..."
git commit -m "Auto: update and push from setup.sh helper" || echo "[GIT] Nothing to commit."

echo "[GIT] Pushing to origin..."
git push origin main

echo "[GIT] Done."
