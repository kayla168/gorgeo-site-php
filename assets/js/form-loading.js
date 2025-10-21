/**
 * Gorgeo Fasteners - Main JavaScript File
 * Contains:
 * 1. Mobile navigation (hamburger menu) logic.
 * 2. Form submission button loading state.
 * 3. Mobile dropdown menu (e.g., "Services") toggle logic.
 */

// --- 1. Mobile Navigation Logic ---
document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.getElementById('hamburger-menu');
    const body = document.body;

    // 汉堡按钮控制侧边菜单展开
    if (hamburger) {
        hamburger.addEventListener('click', function () {
            body.classList.toggle('nav-open');
        });
    }

    // --- 3. Services 下拉菜单点击展开逻辑（仅移动端） ---
    const dropdownLink = document.querySelector('.dropdown > a');
    const dropdownMenu = document.querySelector('.dropdown .dropdown-content');

    function isMobileView() {
        return window.innerWidth <= 768;
    }

    if (dropdownLink && dropdownMenu) {
        dropdownLink.addEventListener('click', function (e) {
            if (isMobileView()) {
                e.preventDefault(); // 阻止跳转
                dropdownMenu.classList.toggle('show');
            }
        });
    }
});


// --- 2. Form Submission Logic ---
function showSendingButton(form) {
    const button = form.querySelector("button[type='submit']");
    if (button) {
        button.disabled = true;
        button.innerText = "Sending...";
        // 灰色背景 + 禁用样式
        button.style.backgroundColor = "#ccc";
        button.style.color = "#666";
        button.style.cursor = "not-allowed";
    }

    // 短暂延迟以确保浏览器有时间渲染按钮的禁用状态
    setTimeout(() => {
        form.submit();
    }, 80);

    return false;
}
