/* MC Forum customisations for Dynmap. Keeps map features intact, except game-chat sending. */
(() => {
  const dictionary = new Map([
    ['Chat', '聊天记录'], ['Send', '发送'], ['Message', '消息'], ['Players', '在线玩家'],
    ['Player', '玩家'], ['Maps', '地图'], ['Map', '地图'], ['Markers', '地标'],
    ['Marker', '地标'], ['Layers', '图层'], ['Layer', '图层'], ['Settings', '设置'],
    ['Zoom in', '放大'], ['Zoom out', '缩小'], ['Home', '返回中心'], ['Link', '链接'],
    ['Follow', '跟随'], ['Hide', '隐藏'], ['Show', '显示'], ['Search', '搜索'],
    ['Loading...', '加载中…'], ['Loading', '加载中'], ['Offline', '离线'], ['Online', '在线'],
    ['No players are online.', '当前没有玩家在线。'], ['No players online', '当前没有玩家在线'],
    ['World', '世界'], ['Coordinates', '坐标']
  ]);
  const sendPattern = /^(send|发送|send message|send chat|send to game)$/i;
  const isChatSender = element => {
    const text = (element.value || element.textContent || element.getAttribute('title') || '').trim();
    const meta = `${element.id || ''} ${element.className || ''} ${element.name || ''} ${element.placeholder || ''}`.toLowerCase();
    return sendPattern.test(text) || /(sendmessage|send-message|chat-send|chat_send|chatinput|chat-input|messageinput)/.test(meta);
  };
  // 移除 Dynmap 内部会跳转到带 worldname/mapname 参数直链的入口。
  const isDirectMapLink = element => {
    const href = (element.getAttribute('href') || '').toLowerCase();
    const text = (element.textContent || element.getAttribute('title') || '').trim().toLowerCase();
    return /[?&](worldname|mapname)=/.test(href) || /^(link|链接|permalink|永久链接)$/.test(text);
  };
  const translateText = text => {
    const value = text.trim();
    return dictionary.get(value) || value
      .replace(/Zoom in/gi, '放大').replace(/Zoom out/gi, '缩小')
      .replace(/Loading\.\.\./gi, '加载中…').replace(/No players are online\./gi, '当前没有玩家在线。');
  };
  const update = () => {
    document.querySelectorAll('button, input[type="button"], input[type="submit"], a, label, input, textarea').forEach(element => {
      if (isChatSender(element)) {
        const wrapper = element.closest('.chatbox, .chat-input, .messageinput, .ui-widget, li, div');
        (wrapper || element).style.display = 'none';
        return;
      }
      if (isDirectMapLink(element)) {
        element.style.display = 'none';
        return;
      }
      if (element.children.length === 0 && element.textContent) {
        const translated = translateText(element.textContent);
        if (translated !== element.textContent.trim()) element.textContent = translated;
      }
      ['title', 'placeholder', 'value'].forEach(attribute => {
        const original = element.getAttribute(attribute);
        if (original && dictionary.has(original.trim())) element.setAttribute(attribute, dictionary.get(original.trim()));
      });
    });
  };
  const observer = new MutationObserver(update);
  observer.observe(document.documentElement, { childList: true, subtree: true });
  document.addEventListener('DOMContentLoaded', () => {
    update();
    if ('serviceWorker' in navigator) navigator.serviceWorker.register('mc-forum-theme-sw.js', { scope: './' }).catch(() => {});
  });
  update();
})();