#!/usr/bin/env bash
set -euo pipefail

# ===== EDIT THESE =====
REPO_URL="https://github.com/YOURUSER/YOURREPO.git"   # use HTTPS for public repos
BRANCH="main"
APP_DIR="$HOME/smart-menu"
# ======================

echo "==> OS update + deps"
sudo dnf -y update
sudo dnf -y install git docker curl

echo "==> Enable/start Docker"
sudo systemctl enable docker
sudo systemctl start docker

echo "==> Add user to docker group (so docker works without sudo)"
if ! groups | grep -q '\bdocker\b'; then
  sudo usermod -aG docker "$USER"
  echo
  echo "IMPORTANT: You were added to the docker group."
  echo "Run this next, then re-run the bootstrap command:"
  echo "  newgrp docker"
  exit 0
fi

echo "==> Install docker-compose (standalone) if missing"
if ! command -v docker-compose >/dev/null 2>&1; then
  sudo curl -L https://github.com/docker/compose/releases/download/v2.27.0/docker-compose-linux-x86_64 \
    -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
fi

echo "==> Clone or update repo"
if [ ! -d "$APP_DIR/.git" ]; then
  git clone --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
else
  git -C "$APP_DIR" pull
fi

echo "==> Done"
echo "Repo is in: $APP_DIR"
docker --version
docker-compose version
