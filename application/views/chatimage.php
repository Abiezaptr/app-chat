<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Image</title>
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/gpt.png') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            flex: 0 0 20%;
            /* Lebar sidebar */
            background-color: #f0f0f0;
            padding: 20px;
            border-right: 1px solid #ccc;
        }

        .chat-container {
            flex: 1;
            /* Mengisi ruang yang tersisa */
            padding: 20px;
            overflow: hidden;
            display: flex;
            /* Menjadikan kontainer sebagai flex container */
            flex-direction: column;
            /* Menjadikan anak-anak dalam kontainer berbaris vertikal */
            justify-content: flex-end;
            /* Memposisikan anak-anak di bagian bawah kontainer */
        }

        .chat {
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            /* Menambahkan scroll vertikal jika konten lebih besar dari area chat */
            max-height: 80vh;
            /* Maksimum tinggi area chat sebelum ditambahkan scroll */
        }

        .message-container {
            display: flex;
            justify-content: flex-start;
        }

        .user-message,
        .system-message {
            word-wrap: break-word;
            /* Memastikan kata-kata terlalu panjang akan dipindahkan ke baris baru */
            overflow: hidden;
            /* Menghindari teks yang keluar dari batas buble */
            max-width: 70%;
            /* Lebar maksimum pesan pengguna dan pesan sistem */
        }

        .user-message {
            margin-left: auto;
            background-color: #212b36;
            color: white;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .system-message {
            margin-right: auto;
            background-color: #f0f0f0;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .input-box {
            display: flex;
            margin-top: 20px;
        }

        .input-box input {
            flex: 1;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            /* Mengatur sudut melengkung untuk membuat input box agak rounded */
            margin-right: 10px;
        }

        .input-box button {
            padding: 10px 20px;
            border: none;
            background-color: #212b36;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Tambahan CSS untuk select */
        .input-box select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <!-- Isi Sidebar di sini -->
            <div class="image-with-text" style="display: flex; align-items: center;">
                <img src="<?= base_url('assets/gpt.png') ?>" alt="Chat Menu" style="width: 20px; height: auto; margin-right: 10px;">
                <span style="font-size: 14px; font-family: Arial, Helvetica, sans-serif;">&nbsp;<strong>New Chat</strong></span>
                <!-- Tambahkan elemen select di sini -->
                <div style="margin-left: auto; display: flex; align-items: center;">
                    <select>
                        <option value="AI Image">AI Image</option>
                        <option value="AI Text">AI Text</option>
                    </select>
                    <i class="fas fa-edit" style="margin-left: 10px;" onclick="refreshPage()"></i>
                </div>
            </div>
        </div>

        <div class="chat-container">
            <div class="chat" id="messages"></div>
            <div class="input-box">
                <input type="text" id="userMessage" placeholder="Type a message...">
                <button id="sendButton">Send</button>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk efek pengetikan animasi
        async function animationWriter(content, element) {
            let i = 0;
            const typingEffect = async () => {
                if (i < content.length) {
                    element.innerText += content.charAt(i);
                    if (content.charAt(i) === " ") {
                        element.innerText += "\u00A0"; // Tambahkan spasi yang tidak dapat dipatahkan untuk spasi
                    }
                    i++;
                    await sleep(5); // Mengatur kecepatan pengetikan (dalam milidetik)
                    typingEffect();
                }
            };
            typingEffect();
        }

        // Fungsi untuk mengatur waktu jeda
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function sendMessage() {
            const userMessage = document.getElementById('userMessage').value.trim();

            if (!userMessage) return; // Jika pesan kosong, jangan kirim

            // Tambahkan pesan pengguna ke dalam daftar pesan
            const userMessageDiv = document.createElement('div');
            userMessageDiv.classList.add('message-container');
            const userMessageContent = document.createElement('div');
            userMessageContent.classList.add('user-message');
            userMessageContent.innerText = userMessage;
            userMessageDiv.appendChild(userMessageContent);
            document.getElementById('messages').appendChild(userMessageDiv);

            // Bersihkan input setelah mengirim pesan
            document.getElementById('userMessage').value = '';

            // Kirim pertanyaan dalam bentuk JSON ke server
            fetch('http://192.168.15.67:5000/imagechat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json' // Mengatur tipe konten menjadi JSON
                    },
                    body: JSON.stringify({
                        question: userMessage
                    }) // Mengirim pertanyaan dalam bentuk JSON
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Handling data dari server
                    const systemMessageDiv = document.createElement('div');
                    systemMessageDiv.classList.add('message-container');
                    const systemMessageContent = document.createElement('div');
                    systemMessageContent.classList.add('system-message');
                    systemMessageDiv.appendChild(systemMessageContent);
                    document.getElementById('messages').appendChild(systemMessageDiv);
                    animationWriter(data.summary, systemMessageContent);
                })
                .catch(error => {
                    console.error('Error:', error.message);
                    const errorMessageDiv = document.createElement('div');
                    errorMessageDiv.classList.add('message-container');
                    const errorMessageContent = document.createElement('div');
                    errorMessageContent.classList.add('system-message');
                    errorMessageContent.innerText = 'Error processing request';
                    errorMessageDiv.appendChild(errorMessageContent);
                    document.getElementById('messages').appendChild(errorMessageDiv);
                });
        }

        // Mendaftarkan event listener untuk tombol 'Send'
        document.getElementById('sendButton').addEventListener('click', sendMessage);

        // Mendaftarkan event listener untuk input teks
        document.getElementById('userMessage').addEventListener('keypress', function(event) {
            // Periksa apakah tombol yang ditekan adalah tombol "Enter"
            if (event.key === 'Enter') {
                // Panggil fungsi sendMessage untuk mengirim pesan
                sendMessage();
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.querySelector('select');

            // Mendaftarkan event listener untuk memantau perubahan pada elemen select
            selectElement.addEventListener('change', function() {
                const selectedOption = this.value;

                // Jika opsi yang dipilih adalah "AI Image", arahkan pengguna ke URL yang ditentukan
                if (selectedOption === 'AI Text') {
                    window.location.href = 'http://192.168.15.67/app-chat';
                }
            });
        });
    </script>

    <script>
        function refreshPage() {
            location.reload();
        }
    </script>


</body>

</html>