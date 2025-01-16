document.addEventListener('DOMContentLoaded', () => {
    const chatbox = document.getElementById('ai-chatbox');
    const sendButton = document.getElementById('send-chat');
    const chatOutput = document.getElementById('chat-output');
    const chatInput = document.getElementById('chat-input');

    if (sendButton) {
        sendButton.addEventListener('click', async () => {
            const userInput = chatInput.value.trim();
            if (!userInput) return;

            chatOutput.innerHTML += `<p><strong>User:</strong> ${userInput}</p>`;
            chatInput.value = '';

            const response = await fetch(aiChatboxVars.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_ai_response',
                    message: userInput,
                    api_key: aiChatboxVars.openai_api_key,
                }),
            });

            const data = await response.json();
            if (data.success) {
                chatOutput.innerHTML += `<p><strong>AI:</strong> ${data.data.response}</p>`;
            } else {
                chatOutput.innerHTML += `<p><strong>Error:</strong> ${data.data.message}</p>`;
            }
        });
    }
});
