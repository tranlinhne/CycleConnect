document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const profileForm = document.getElementById("profileForm");
    const changePasswordForm = document.getElementById("changePasswordForm");

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhone(phone) {
        return /^[0-9]{9,11}$/.test(phone);
    }

    function clearError(id) {
        const el = document.getElementById(id);
        if (el) el.innerText = "";
    }

    function setError(id, message) {
        const el = document.getElementById(id);
        if (el) el.innerText = message;
    }

    async function postData(url, data) {
        const response = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        return response.json();
    }

    async function getData(url) {
        const response = await fetch(url);
        return response.json();
    }

    if (loginForm) {
        loginForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const email = document.getElementById("loginEmail").value.trim();
            const password = document.getElementById("loginPassword").value.trim();

            clearError("loginEmailError");
            clearError("loginPasswordError");

            let isValid = true;

            if (email === "") {
                setError("loginEmailError", "Vui lòng nhập email");
                isValid = false;
            } else if (!isValidEmail(email)) {
                setError("loginEmailError", "Email không hợp lệ");
                isValid = false;
            }

            if (password === "") {
                setError("loginPasswordError", "Vui lòng nhập mật khẩu");
                isValid = false;
            }

            if (!isValid) return;

            try {
                const result = await postData("api/login.php", { email, password });
                if (result.success) {
                    alert(result.message || "Đăng nhập thành công");
                    window.location.href = "profile.php";
                } else {
                    alert(result.message || "Đăng nhập thất bại");
                }
            } catch (error) {
                alert("Không thể kết nối server");
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const full_name = document.getElementById("registerName").value.trim();
            const email = document.getElementById("registerEmail").value.trim();
            const phone = document.getElementById("registerPhone").value.trim();
            const password = document.getElementById("registerPassword").value.trim();
            const confirm_password = document.getElementById("registerConfirmPassword").value.trim();

            clearError("registerNameError");
            clearError("registerEmailError");
            clearError("registerPhoneError");
            clearError("registerPasswordError");
            clearError("registerConfirmPasswordError");

            let isValid = true;

            if (full_name === "") {
                setError("registerNameError", "Vui lòng nhập họ tên");
                isValid = false;
            }

            if (email === "") {
                setError("registerEmailError", "Vui lòng nhập email");
                isValid = false;
            } else if (!isValidEmail(email)) {
                setError("registerEmailError", "Email không hợp lệ");
                isValid = false;
            }

            if (phone === "") {
                setError("registerPhoneError", "Vui lòng nhập số điện thoại");
                isValid = false;
            } else if (!isValidPhone(phone)) {
                setError("registerPhoneError", "Số điện thoại không hợp lệ");
                isValid = false;
            }

            if (password === "") {
                setError("registerPasswordError", "Vui lòng nhập mật khẩu");
                isValid = false;
            } else if (password.length < 6) {
                setError("registerPasswordError", "Mật khẩu phải từ 6 ký tự");
                isValid = false;
            }

            if (confirm_password === "") {
                setError("registerConfirmPasswordError", "Vui lòng nhập lại mật khẩu");
                isValid = false;
            } else if (confirm_password !== password) {
                setError("registerConfirmPasswordError", "Mật khẩu nhập lại không khớp");
                isValid = false;
            }

            if (!isValid) return;

            try {
                const result = await postData("api/register.php", {
                    full_name,
                    email,
                    phone,
                    password
                });

                if (result.success) {
                    alert(result.message || "Đăng ký thành công");
                    window.location.href = "login.php";
                } else {
                    alert(result.message || "Đăng ký thất bại");
                }
            } catch (error) {
                alert("Không thể kết nối server");
            }
        });
    }

    if (profileForm) {
        loadProfile();

        profileForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const full_name = document.getElementById("profileName").value.trim();
            const email = document.getElementById("profileEmail").value.trim();
            const phone = document.getElementById("profilePhone").value.trim();
            const address = document.getElementById("profileAddress").value.trim();

            clearError("profileNameError");
            clearError("profileEmailError");
            clearError("profilePhoneError");
            clearError("profileAddressError");

            let isValid = true;

            if (full_name === "") {
                setError("profileNameError", "Vui lòng nhập họ tên");
                isValid = false;
            }

            if (email === "") {
                setError("profileEmailError", "Vui lòng nhập email");
                isValid = false;
            } else if (!isValidEmail(email)) {
                setError("profileEmailError", "Email không hợp lệ");
                isValid = false;
            }

            if (phone === "") {
                setError("profilePhoneError", "Vui lòng nhập số điện thoại");
                isValid = false;
            } else if (!isValidPhone(phone)) {
                setError("profilePhoneError", "Số điện thoại không hợp lệ");
                isValid = false;
            }

            if (address === "") {
                setError("profileAddressError", "Vui lòng nhập địa chỉ");
                isValid = false;
            }

            if (!isValid) return;

            try {
                const result = await postData("api/update-profile.php", {
                    full_name,
                    email,
                    phone,
                    address
                });

                if (result.success) {
                    alert(result.message || "Cập nhật thành công");
                } else {
                    alert(result.message || "Cập nhật thất bại");
                }
            } catch (error) {
                alert("Không thể kết nối server");
            }
        });
    }

    if (changePasswordForm) {
        changePasswordForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const old_password = document.getElementById("oldPassword").value.trim();
            const new_password = document.getElementById("newPassword").value.trim();
            const confirm_new_password = document.getElementById("confirmNewPassword").value.trim();

            clearError("oldPasswordError");
            clearError("newPasswordError");
            clearError("confirmNewPasswordError");

            let isValid = true;

            if (old_password === "") {
                setError("oldPasswordError", "Vui lòng nhập mật khẩu cũ");
                isValid = false;
            }

            if (new_password === "") {
                setError("newPasswordError", "Vui lòng nhập mật khẩu mới");
                isValid = false;
            } else if (new_password.length < 6) {
                setError("newPasswordError", "Mật khẩu mới phải từ 6 ký tự");
                isValid = false;
            }

            if (confirm_new_password === "") {
                setError("confirmNewPasswordError", "Vui lòng nhập lại mật khẩu mới");
                isValid = false;
            } else if (confirm_new_password !== new_password) {
                setError("confirmNewPasswordError", "Mật khẩu nhập lại không khớp");
                isValid = false;
            }

            if (!isValid) return;

            try {
                const result = await postData("api/change-password.php", {
                    old_password,
                    new_password
                });

                if (result.success) {
                    alert(result.message || "Đổi mật khẩu thành công");
                    window.location.href = "profile.php";
                } else {
                    alert(result.message || "Đổi mật khẩu thất bại");
                }
            } catch (error) {
                alert("Không thể kết nối server");
            }
        });
    }

    async function loadProfile() {
        const profileName = document.getElementById("profileName");
        if (!profileName) return;

        try {
            const result = await getData("api/get-profile.php");
            if (result.success && result.user) {
                document.getElementById("profileName").value = result.user.full_name || "";
                document.getElementById("profileEmail").value = result.user.email || "";
                document.getElementById("profilePhone").value = result.user.phone || "";
                document.getElementById("profileAddress").value = result.user.address || "";
            }
        } catch (error) {
            console.log(error);
        }
    }
});