/* MC Forum customisations for Dynmap: presentation only; no map data is changed. */
(() => {
  const sendPattern = /^(send|发送|发言|聊天发送|send message|send chat|send to game)$/i;
  const hideGameChatSender = () => {
    document.querySelectorAll('button, input[type="button"], input[type="submit"], a, label').forEach((element) => {
      const text = (element.value || element.textContent || element.getAttribute('title') || '').trim();
      const id = `${element.id || ''} ${element.className || ''}`.toLowerCase();
      if (sendPattern.test(text) || /(sendmessage|send-message|chat-send|chat_send)/.test(id)) {
        element.style.display = 'none';
      }
    });

    document.querySelectorAll('input, textarea').forEach((element) => {
      const id = `${element.id || ''} ${element.className || ''} ${element.name || ''} ${element.placeholder || ''}`.toLowerCase();
      if (/(sendmessage|send-message|chat-send|chat_send|chatinput|chat-input)/.test(id)) {
        const wrapper = element.closest('.ui-widget, .chatbox, .chat-input, .messageinput, li, div');
        if (wrapper) wrapper.style.display = 'none';
        else element.style.display = 'none';
      }
    });
  };

  const observer = new MutationObserver(hideGameChatSender);
  observer.observe(document.documentElement, { childList: true, subtree: true });
  document.addEventListener('DOMContentLoaded', hideGameChatSender);
  hideGameChatSender();
})();
