#!/bin/bash
# 自动检测更改并推送到 GitHub
# 用于 crontab 定时任务

REPO_DIR="/workspace/mc-forum"
cd "$REPO_DIR" || exit 1

# 检查是否有未提交的更改
if [ -z "$(git status --porcelain)" ]; then
    exit 0
fi

# 有更改：自动提交并推送
git add -A
git commit -m "auto: $(date '+%Y-%m-%d %H:%M:%S') — 自动同步更改" 2>/dev/null
git push 2>/dev/null