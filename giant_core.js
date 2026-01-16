// إعدادات المحرك الذكي
const API_TOKEN = "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";
const MODEL_ID = "Qwen/Qwen2.5-72B-Instruct";

const messagesContainer = document.getElementById('chat-messages');
const userInput = document.getElementById('chat-input');
const sendBtn = document.getElementById('send-btn');
const voiceBtn = document.getElementById('voice-btn');
const clearBtn = document.getElementById('clear-btn');
const typingIndicator = document.getElementById('typing');

// وظيفة الاتصال بذكاء العملاق
async function askGiant(text) {
    typingIndicator.style.display = 'block';
    try {
        const response = await fetch(`https://api-inference.huggingface.co/models/${MODEL_ID}`, {
            headers: {
                "Authorization": `Bearer ${API_TOKEN}`,
                "Content-Type": "application/json",
            },
            method: "POST",
            body: JSON.stringify({
                inputs: `<|im_start|>system\nأنت 'The Giant AI' مساعد ذكي خارق لمنصة العملاق. نادِ الطالب بـ 'يا عملاق'. أجب بنحو سليم تماماً وتنسيق احترافي.<|im_end|>\n<|im_start|>user\n${text}<|im_end|>\n<|im_start|>assistant`,
                parameters: { 
                    max_new_tokens: 1024, 
                    temperature: 0.1, // لضمان دقة النحو وعدم التخريف
                    top_p: 0.9 
                }
            }),
        });

        const result = await response.json();
        typingIndicator.style.display = 'none';

        let output = "";
        if (Array.isArray(result) && result[0].generated_text) {
            // تنظيف الرد من الرموز الزائدة
            output = result[0].generated_text.split('<|im_start|>assistant').pop().trim();
        } else if (result.error) {
            output = "يا عملاق، السيرفر حالياً تحت ضغط كبير. جرب تبعت رسالتك كمان شوية.";
        } else {
            output = "يا عملاق، واجهت مشكلة بسيطة في معالجة البيانات، حاول مرة ثانية.";
        }
        
        appendMessage(output, 'bot');
    } catch (error) {
        typingIndicator.style.display = 'none';
        console.error("Connection Error:", error);
        appendMessage("يا عملاق، اتأكد إنك فاتح الموقع من رابط آمن (HTTPS) عشان أقدر أكلم السيرفر والمايك يشتغل.", 'bot');
    }
}

// إضافة الرسالة للشاشة
function appendMessage(text, side) {
    const msgDiv = document.createElement('div');
    msgDiv.className = `message ${side}-message`;
    // استخدام مكتبة marked لتحويل الـ Markdown لـ HTML منسق
    msgDiv.innerHTML = side === 'bot' ? marked.parse(text) : text;
    messagesContainer.appendChild(msgDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// وظيفة الإرسال
function handleSend() {
    const text = userInput.value.trim();
    if (!text) return;
    appendMessage(text, 'user');
    userInput.value = '';
    askGiant(text);
}

// تشغيل المايك (التعرف على الصوت)
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
if (SpeechRecognition) {
    const recognition = new SpeechRecognition();
    recognition.lang = 'ar-SA';

    voiceBtn.onclick = () => {
        try {
            recognition.start();
            voiceBtn.classList.add('active');
        } catch(e) { recognition.stop(); }
    };

    recognition.onresult = (e) => {
        userInput.value = e.results[0][0].transcript;
        voiceBtn.classList.remove('active');
        handleSend();
    };

    recognition.onerror = () => voiceBtn.classList.remove('active');
    recognition.onend = () => voiceBtn.classList.remove('active');
} else {
    voiceBtn.style.display = 'none'; // إخفاء الزر لو المتصفح قديم
}

// أزرار التحكم
sendBtn.onclick = handleSend;
userInput.onkeypress = (e) => { if (e.key === 'Enter') handleSend(); };
clearBtn.onclick = () => { messagesContainer.innerHTML = ''; appendMessage("تم مسح المحادثة. أنا جاهز من جديد يا عملاق!", "bot"); };

// رسالة الترحيب
window.onload = () => {
    appendMessage("أهلاً بك يا عملاق! محرك البحث والذكاء الاصطناعي الآن في خدمتك. اسألني في أي مادة أو اطلب مني إعراب أي جملة.", "bot");
};
