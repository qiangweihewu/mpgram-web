import './style.css'

document.querySelector('#app').innerHTML = `
  <div class="login-container">
    <div class="login-header">
      <h1>MPGram Web</h1>
      <p class="login-subtitle">Your secure gateway to Telegram</p>
    </div>
    
    <div class="login-box">
      <div class="login-methods">
        <div class="phone-login">
          <h2>Phone Number</h2>
          <form action="login.php" method="post" class="login-form">
            <input type="text" class="login-input" placeholder="+1234567890" name="phone">
            <button type="submit" class="login-button">Continue</button>
          </form>
        </div>
        
        <div class="qr-login">
          <h2>QR Code Login</h2>
          <a href="qrlogin.php" class="qr-button">Scan QR Code</a>
        </div>
      </div>
    </div>

    <div class="login-footer">
      <a href="about.php">About</a>
      <a href="login.php?lang=en">English</a>
      <a href="login.php?lang=ru">Русский</a>
    </div>
  </div>
`