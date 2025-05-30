jQuery(document).ready(function($) {
    const sendButton = $('#ai-send-message');
    const userInput = $('#ai-user-input');
    const chatMessages = $('#ai-chat-messages');
    let isProcessing = false;

    // Markdown Configuration
    marked.setOptions({
        highlight: function(code, lang) {
            if (lang && hljs.getLanguage(lang)) {
                try {
                    return hljs.highlight(code, { language: lang }).value;
                } catch (e) {}
            }
            return hljs.highlightAuto(code).value;
        },
        breaks: true
    });

    // Event Listener for Send Button and Enter Key
    sendButton.on('click', sendMessage);
    userInput.on('keydown', function(e) {
        if (e.keyCode === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Send Message
    function sendMessage() {
        if (isProcessing) return;
        
        const message = userInput.val().trim();
        if (!message) return;

        isProcessing = true;
        sendButton.prop('disabled', true);
        
        appendMessage('user', message);
        appendTypingIndicator();
        userInput.val('');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'proteczone_ai_assistant_message',
                message: message,
                nonce: proteczoneAiAssistant.nonce
            },
            success: function(response) {
                removeTypingIndicator();
                if (response.success) {
                    appendMessage('assistant', response.data.message);
                } else {
                    appendMessage('system', 'Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                removeTypingIndicator();
                appendMessage('system', 'Network Error: ' + error);
            },
            complete: function() {
                isProcessing = false;
                sendButton.prop('disabled', false);
            }
        });
    }

    // Add Message to Chat
    function appendMessage(type, content) {
        const messageDiv = $('<div>')
            .addClass('ai-message')
            .addClass(type);
        
        if (type === 'assistant') {
            // Markdown for Claude's responses
            messageDiv.html(marked(content));
            // Syntax Highlighting for code
            messageDiv.find('pre code').each(function(i, block) {
                hljs.highlightBlock(block);
            });
        } else {
            messageDiv.text(content);
        }
        
        chatMessages.append(messageDiv);
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }

    // Add Loading Animation
    function appendTypingIndicator() {
        $('<div>')
            .addClass('ai-message typing')
            .html('<div class="typing-indicator"><span></span><span></span><span></span></div>')
            .appendTo(chatMessages);
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }

    // Remove Loading Animation
    function removeTypingIndicator() {
        chatMessages.find('.typing').remove();
    }

    // Optional: Save Chat History
    function saveChatHistory() {
        const messages = [];
        $('.ai-message').each(function() {
            const type = $(this).hasClass('user') ? 'user' : 'assistant';
            messages.push({
                type: type,
                content: type === 'user' ? $(this).text() : $(this).html()
            });
        });
        localStorage.setItem('wpAiChatHistory', JSON.stringify(messages));
    }

    // Optional: Load Chat History
    function loadChatHistory() {
        const history = localStorage.getItem('wpAiChatHistory');
        if (history) {
            const messages = JSON.parse(history);
            messages.forEach(msg => {
                const messageDiv = $('<div>')
                    .addClass('ai-message')
                    .addClass(msg.type);
                if (msg.type === 'assistant') {
                    messageDiv.html(msg.content);
                } else {
                    messageDiv.text(msg.content);
                }
                chatMessages.append(messageDiv);
            });
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }
    }

    // Restore Chat History on Load
    loadChatHistory();
});
