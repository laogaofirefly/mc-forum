/**
 * 游戏聊天模块 - 重构版
 *
 * 架构说明：
 * - ChatStore：状态管理（lastId、消息缓存、用户信息）
 * - ChatAPI：所有后端请求封装
 * - ChatView：DOM 渲染与交互
 * - ChatApp：主控制器，协调上述模块
 *
 * 使用原生 JS + IIFE，无外部依赖，避免与全局脚本冲突
 */
(function() {
    'use strict';

    // 防止重复初始化（浏览器缓存可能导致脚本执行多次）
    if (window.__chatAppInitialized) return;
    window.__chatAppInitialized = true;

    // ==================== 配置 ====================
    var CONFIG = {
        fetchInterval: 3000,      // 拉取新消息间隔
        syncInterval: 10000,      // 同步 MC 日志间隔
        scrollThreshold: 80,      // 判定在底部的阈值
        endpoints: {
            fetch: window.__CHAT_ROUTES__.fetch,
            send: window.__CHAT_ROUTES__.send,
            demo: window.__CHAT_ROUTES__.demo,
            sync: window.__CHAT_ROUTES__.sync,
            logPreview: window.__CHAT_ROUTES__.logPreview
        }
    };

    // ==================== 工具函数 ====================
    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function safeJson(res) {
        // 容错处理：即使后端返回 HTML 错误页也不崩溃
        return res.text().then(function(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                return { ok: false, message: '服务器返回了非 JSON 数据' };
            }
        });
    }

    // ==================== State Store ====================
    var Store = {
        currentUser: window.__CHAT_USER__,
        lastId: window.__CHAT_LAST_ID__ || 0,
        sending: false,
        autoScroll: true,
        messageIds: new Set(),  // 已渲染的消息 ID，避免重复
    };

    // ==================== API 层 ====================
    var API = {
        fetch: function(afterId) {
            return fetch(CONFIG.endpoints.fetch + '?after_id=' + afterId + '&_t=' + Date.now(), {
                credentials: 'same-origin'
            }).then(safeJson);
        },
        send: function(message) {
            var formData = new FormData();
            formData.append('message', message);
            return fetch(CONFIG.endpoints.send, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: formData
            }).then(safeJson);
        },
        demo: function() {
            return fetch(CONFIG.endpoints.demo, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.__CSRF_TOKEN__,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: '{}'
            }).then(safeJson);
        },
        sync: function() {
            return fetch(CONFIG.endpoints.sync, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.__CSRF_TOKEN__,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: '{}'
            }).then(safeJson);
        },
        logPreview: function() {
            return fetch(CONFIG.endpoints.logPreview + '?_t=' + Date.now(), {
                credentials: 'same-origin'
            }).then(safeJson);
        }
    };

    // ==================== View 层 ====================
    var View = {
        els: {},

        init: function() {
            this.els = {
                chatBody: document.getElementById('chatBody'),
                emptyTip: document.getElementById('emptyTip'),
                statusEl: document.getElementById('chatStatus'),
                demoBtn: document.getElementById('demoMsgBtn'),
                syncBtn: document.getElementById('syncLogBtn'),
                sendForm: document.getElementById('sendForm'),
                sendInput: document.getElementById('sendInput'),
                sendBtn: document.getElementById('sendBtn'),
                sendHint: document.getElementById('sendHint'),
                toggleLogBtn: document.getElementById('toggleLogPreview'),
                logWrap: document.getElementById('logPreviewWrap'),
                loadLogBtn: document.getElementById('loadLogPreview'),
                logBody: document.getElementById('logPreviewBody'),
                logMeta: document.getElementById('logPreviewMeta')
            };
        },

        scrollToBottom: function() {
            var body = this.els.chatBody;
            if (!body) return;
            body.scrollTop = body.scrollHeight;
        },

        isAtBottom: function() {
            var body = this.els.chatBody;
            if (!body) return true;
            return body.scrollHeight - body.clientHeight - body.scrollTop < CONFIG.scrollThreshold;
        },

        setStatus: function(text, type) {
            var el = this.els.statusEl;
            if (!el) return;
            var colors = {
                green: ['#ecfdf5', '#047857', '#a7f3d0'],
                yellow: ['#fffbeb', '#b45309', '#fde68a'],
                red: ['#fef2f2', '#b91c1c', '#fecaca']
            };
            var c = colors[type] || colors.green;
            el.style.background = c[0];
            el.style.color = c[1];
            el.style.borderColor = c[2];
            el.innerHTML = text;
        },

        appendMessage: function(m) {
            var body = this.els.chatBody;
            if (!body || !m) return;

            // 隐藏空提示
            var tip = this.els.emptyTip;
            if (tip) tip.style.display = 'none';

            // 去重
            if (m.id) {
                if (Store.messageIds.has(m.id)) return;
                Store.messageIds.add(m.id);
            }

            var isSelf = Store.currentUser && m.player_name === Store.currentUser;
            var row = document.createElement('div');
            row.className = 'chat-row-qq' + (isSelf ? ' self' : '');
            if (m.id) row.setAttribute('data-id', m.id);
            row.innerHTML =
                '<div class="chat-name">' + escapeHtml(m.player_name) + '</div>' +
                '<div class="chat-bubble ' + (isSelf ? 'self' : 'others') + '">' + escapeHtml(m.message) + '</div>';
            body.appendChild(row);
        },

        setSendBtnState: function(disabled, text) {
            var btn = this.els.sendBtn;
            if (!btn) return;
            btn.disabled = disabled;
            if (text) btn.textContent = text;
        },

        setSendHint: function(text, type) {
            var hint = this.els.sendHint;
            if (!hint) return;
            hint.textContent = text;
            hint.style.color = type === 'error' ? '#dc2626' :
                               type === 'success' ? '#059669' :
                               type === 'warning' ? '#b45309' : '#64748b';
        },

        resetSendInput: function() {
            var input = this.els.sendInput;
            if (!input) return;
            input.value = '';
            input.style.height = 'auto';
        },

        focusInput: function() {
            var input = this.els.sendInput;
            if (input) input.focus();
        },

        toggleLogPanel: function(show) {
            var wrap = this.els.logWrap;
            var btn = this.els.toggleLogBtn;
            if (!wrap) return;
            wrap.style.display = show ? 'block' : 'none';
            if (btn) btn.textContent = show ? '▼ 收起日志预览' : '▶ 查看日志解析情况';
        },

        renderLogPreview: function(data) {
            var body = this.els.logBody;
            var meta = this.els.logMeta;
            if (!body) return;

            if (!data.ok) {
                body.innerHTML = '<div style="color:#dc2626;">错误：' + escapeHtml(data.error || '未知') + '</div>';
                return;
            }

            if (meta) meta.textContent = '路径：' + data.log_path;
            var rows = data.rows || [];
            if (rows.length === 0) {
                body.innerHTML = '<div style="color:#64748b;">日志为空</div>';
                return;
            }

            var chatCount = 0;
            var html = '';
            rows.forEach(function(r) {
                var isChat = r.is_chat;
                if (isChat) chatCount++;
                var dotColor = isChat ? '#10b981' : '#94a3b8';
                var textColor = isChat ? '#047857' : '#64748b';
                var parsed = '';
                if (isChat && r.parsed) {
                    parsed = '<span style="color:#059669;margin-left:8px;">→ [' + escapeHtml(r.parsed.player) + '] ' + escapeHtml(r.parsed.message) + '</span>';
                }
                html +=
                    '<div style="color:' + textColor + ';display:flex;align-items:flex-start;gap:8px;margin-bottom:4px;">' +
                        '<span style="display:inline-block;width:8px;height:8px;margin-top:6px;border-radius:50%;flex-shrink:0;background:' + dotColor + ';"></span>' +
                        '<div style="flex:1;"><span style="word-break:break-all;">' + escapeHtml(r.raw) + '</span>' + parsed + '</div>' +
                    '</div>';
            });
            body.innerHTML = html;
            if (meta) meta.textContent = '路径：' + data.log_path + '　|　共 ' + rows.length + ' 行，' + chatCount + ' 行聊天';
        }
    };

    // ==================== App 主控制器 ====================
    var App = {
        timers: {},

        init: function() {
            View.init();
            this.collectExistingIds();
            this.bindEvents();
            this.startPolling();
            this.initialScroll();
        },

        // 收集页面初始渲染的消息 ID（避免重复添加）
        collectExistingIds: function() {
            var rows = document.querySelectorAll('.chat-row-qq[data-id]');
            for (var i = 0; i < rows.length; i++) {
                var id = rows[i].getAttribute('data-id');
                if (id) Store.messageIds.add(parseInt(id, 10));
            }
        },

        bindEvents: function() {
            var self = this;

            // 滚动监听 - 判断是否在底部
            var body = View.els.chatBody;
            if (body) {
                body.addEventListener('scroll', function() {
                    Store.autoScroll = View.isAtBottom();
                });
            }

            // 输入框自动增高
            var input = View.els.sendInput;
            if (input) {
                input.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                });
            }

            // 发送表单
            var form = View.els.sendForm;
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.handleSend();
                });
            }

            // 测试消息按钮
            var demoBtn = View.els.demoBtn;
            if (demoBtn) {
                demoBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.handleDemo();
                });
            }

            // 同步日志按钮
            var syncBtn = View.els.syncBtn;
            if (syncBtn) {
                syncBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.handleSync();
                });
            }

            // 日志预览面板
            var toggleBtn = View.els.toggleLogBtn;
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var wrap = View.els.logWrap;
                    var show = wrap && wrap.style.display === 'none';
                    View.toggleLogPanel(show);
                    if (show && View.els.logBody && View.els.logBody.children.length <= 1) {
                        self.handleLoadLog();
                    }
                });
            }

            var loadBtn = View.els.loadLogBtn;
            if (loadBtn) {
                loadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.handleLoadLog();
                });
            }
        },

        // 发送消息
        handleSend: function() {
            if (Store.sending) return;
            var input = View.els.sendInput;
            if (!input) return;
            var msg = input.value.trim();
            if (!msg) return;

            Store.sending = true;
            View.setSendBtnState(true, '发送中');
            View.setSendHint('正在发送到游戏内玩家...', 'warning');

            var self = this;
            API.send(msg)
                .then(function(data) {
                    if (data && data.ok) {
                        // 添加自己发的消息到界面
                        if (data.record) {
                            View.appendMessage(data.record);
                            if (data.record.id && data.record.id > Store.lastId) {
                                Store.lastId = data.record.id;
                            }
                        }
                        View.resetSendInput();
                        View.setSendHint('✓ 已发送到游戏', 'success');
                        // 强制滚动到底部
                        Store.autoScroll = true;
                        View.scrollToBottom();
                        setTimeout(function() { View.scrollToBottom(); }, 50);
                        setTimeout(function() { View.scrollToBottom(); }, 200);
                        // 3秒后恢复提示
                        setTimeout(function() {
                            View.setSendHint(self.defaultHint(), 'default');
                        }, 3000);
                    } else {
                        var errMsg = (data && data.message) ? data.message : '发送失败';
                        if (data && data.errors && data.errors.message) {
                            errMsg = data.errors.message[0];
                        }
                        View.setSendHint('✗ ' + errMsg, 'error');
                    }
                })
                .catch(function(e) {
                    View.setSendHint('✗ 网络错误：' + (e.message || e), 'error');
                })
                .finally(function() {
                    Store.sending = false;
                    View.setSendBtnState(false, '发送');
                    View.focusInput();
                });
        },

        // 测试消息
        handleDemo: function() {
            var btn = View.els.demoBtn;
            if (!btn) return;
            btn.disabled = true;
            btn.textContent = '发送中...';
            API.demo()
                .then(function(data) {
                    if (data && data.ok && data.message) {
                        View.appendMessage(data.message);
                        if (data.message.id && data.message.id > Store.lastId) {
                            Store.lastId = data.message.id;
                        }
                        Store.autoScroll = true;
                        View.scrollToBottom();
                    }
                })
                .catch(function() {})
                .finally(function() {
                    btn.disabled = false;
                    btn.textContent = '🧪 测试';
                });
        },

        // 同步日志
        handleSync: function() {
            var btn = View.els.syncBtn;
            if (!btn) return;
            btn.disabled = true;
            btn.textContent = '同步中...';
            API.sync()
                .then(function(data) {
                    if (data && data.ok) {
                        // 同步后立即拉取新消息
                        App.fetchMessages();
                        if (data.inserted > 0) {
                            View.setStatus('<span style="color:#10b981;">●</span> 新增 ' + data.inserted + ' 条', 'green');
                        } else {
                            View.setStatus('<span style="color:#10b981;">●</span> 已同步', 'green');
                        }
                    } else {
                        View.setStatus('<span style="color:#ef4444;">●</span> ' + (data.message || '同步失败'), 'red');
                    }
                })
                .catch(function() {
                    View.setStatus('<span style="color:#ef4444;">●</span> 同步异常', 'red');
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.textContent = '📜 同步';
                });
        },

        // 加载日志预览
        handleLoadLog: function() {
            var btn = View.els.loadLogBtn;
            var body = View.els.logBody;
            if (!body) return;
            if (btn) {
                btn.disabled = true;
                btn.textContent = '读取中...';
            }
            body.innerHTML = '<div style="color:#b45309;">读取中...</div>';
            API.logPreview()
                .then(function(data) {
                    View.renderLogPreview(data);
                })
                .catch(function(e) {
                    body.innerHTML = '<div style="color:#dc2626;">读取失败：' + escapeHtml(e.message || e) + '</div>';
                })
                .finally(function() {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = '读取最近 30 行日志';
                    }
                });
        },

        // 拉取新消息
        fetchMessages: function() {
            var self = this;
            API.fetch(Store.lastId)
                .then(function(data) {
                    if (data && data.ok && data.messages && data.messages.length > 0) {
                        var wasAtBottom = View.isAtBottom();
                        data.messages.forEach(function(m) {
                            View.appendMessage(m);
                            if (m.id && m.id > Store.lastId) Store.lastId = m.id;
                        });
                        if (wasAtBottom) {
                            View.scrollToBottom();
                        }
                        View.setStatus('<span style="color:#10b981;">●</span> 在线', 'green');
                    } else if (data && data.ok) {
                        View.setStatus('<span style="color:#10b981;">●</span> 在线', 'green');
                    } else {
                        View.setStatus('<span style="color:#f59e0b;">●</span> ' + (data.message || '数据异常'), 'yellow');
                    }
                })
                .catch(function() {
                    View.setStatus('<span style="color:#ef4444;">●</span> 刷新失败', 'red');
                });
        },

        // 初始滚动到底部
        initialScroll: function() {
            View.scrollToBottom();
            var delays = [50, 100, 200, 500, 1000, 1500];
            for (var i = 0; i < delays.length; i++) {
                setTimeout(function() { View.scrollToBottom(); }, delays[i]);
            }
            // 页面完全加载后再滚一次
            if (document.readyState === 'complete') {
                View.scrollToBottom();
            } else {
                window.addEventListener('load', function() {
                    View.scrollToBottom();
                    setTimeout(function() { View.scrollToBottom(); }, 100);
                    setTimeout(function() { View.scrollToBottom(); }, 500);
                });
            }
        },

        // 启动定时器
        startPolling: function() {
            var self = this;
            this.timers.fetch = setInterval(function() { self.fetchMessages(); }, CONFIG.fetchInterval);
            // 后台静默同步日志（不影响 UI）
            this.timers.sync = setInterval(function() {
                API.sync().catch(function() {});
            }, CONFIG.syncInterval);
        },

        defaultHint: function() {
            return window.__CHAT_DEFAULT_HINT__ || '以你的名义发送给游戏内所有在线玩家';
        }
    };

    // ==================== 启动 ====================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { App.init(); });
    } else {
        App.init();
    }
})();
