async function handleLogin(e) {
    e.preventDefault();
    
    // 1. 抓取 HTML 登入表單輸入框的值（請確認你的 HTML input id 是否為 loginUser 和 loginPw）
    const email = document.getElementById('loginUser').value.trim();
    const pw = document.getElementById('loginPw').value;

    // 🛑 前端第一道防線：防呆檢查
    if (!email || !pw) {
        alert("❌ 請輸入帳號與密碼！");
        return;
    }

    // 🎯 關鍵對接：打包成 JSON。
    // 因為你的 PHP 寫的是 $data['username']，所以這裡的 Key 必須叫做 username
    const loginData = {
        username: email,
        password: pw
    };

    try {
        // 🎯 發送請求到你的後端 login.php
        const response = await fetch('./login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(loginData)
        });
        
        const result = await response.json();

        // 2. 根據後端回傳的 status 進行判斷
        if (result.status === 'success') {
            // 登入成功，將帳號寫入前端 localStorage 快取以維持登入狀態
            localStorage.setItem('user', result.username);
            
            // 如果你原本前端有用到姓名快取（例如顯示 "歡迎 XXX"），這裏預設用帳號
            if (!localStorage.getItem('regName_' + result.username)) {
                localStorage.setItem('regName_' + result.username, result.username);
            }

            // 關閉 Bootstrap 登入 Modal 彈窗
            const modalEl = document.getElementById('loginModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // 清空輸入框
            document.getElementById('loginUser').value = '';
            document.getElementById('loginPw').value = '';

            // 觸發你網頁原本更新 UI 的功能（切換右上角按鈕、載入購物車等）
            checkUserStatus();
            loadUserCart();
            updateCartUI();
            showSection('home');
            
            alert("👋 歡迎回來！登入成功。");
        } else {
            // 登入失敗，跳出後端回傳的錯誤訊息（例如："帳號或密碼錯誤，請重新確認！"）
            alert("❌ " + result.message);
        }

    } catch (error) {
        console.error("登入連線發生錯誤:", error);
        alert("結帳系統連線失敗或後端程式錯誤！請檢查 F12 Network 畫面。");
    }
}