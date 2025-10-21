/**
 * 整合后的全功能 JavaScript 文件 (最终优化版 V2)
 * 包含:
 * 1. 动态加载 HTML 公共部分 (页头、页脚、侧边栏、作者信息)。
 * 2. 导航链接 'active' 状态的智能高亮。
 * 3. 移动端导航 (汉堡菜单) 交互。
 * 4. 移动端下拉菜单点击展开。
 * 5. 表单提交按钮的“发送中”状态。
 * 6. 路径+meta双策略动态加载 sidebar。
 * 7. ✅ 最终版：Favicon 全家桶的动态注入 (根据你的实际文件精准匹配)。
 */

// ===================================================================
// ✅ 表单提交函数 (保持不变)
// ===================================================================
function showSendingButton(form) {
  const button = form.querySelector("button[type='submit']");
  if (button) {
    button.disabled = true;
    button.innerText = "Sending...";
    button.style.backgroundColor = "#ccc";
    button.style.color = "#666";
    button.style.cursor = "not-allowed";
  }
  setTimeout(() => {
    form.submit();
  }, 80);
  return false;
}

// ===================================================================
// ✅ includes.js 核心逻辑
// ===================================================================
(function () {

  // --- ✅ 最终版模块: Favicon 注入 ---
  // 1. 定义与你文件完全匹配的“神兵清单”
  const faviconData = [
    // 老祖宗的佩剑 (for IE & legacy browsers)
    { rel: 'shortcut icon', href: 'favicon.ico' },
    
    // 苹果派的徽章 (for iOS home screen)
    { rel: 'apple-touch-icon', sizes: '180x180', href: 'apple-touch-icon.png' }, // 180x180是推荐尺寸，你的文件会自动适配
    
    // 现代浏览器的主要图标
    // RealFaviconGenerator 通常会生成 32x32 和 16x16，你可以把它们也加上，如果没有就只留96x96
    { rel: 'icon', type: 'image/png', sizes: '96x96', href: 'favicon-96x96.png' },
    // { rel: 'icon', type: 'image/png', sizes: '32x32', href: 'favicon-32x32.png' }, // 如果你有这个文件，就取消这行的注释
    // { rel: 'icon', type: 'image/png', sizes: '16x16', href: 'favicon-16x16.png' }, // 如果你有这个文件，就取消这行的注释

    // 未来战甲的总纲 (for Android & PWA)
    { rel: 'manifest', href: 'site.webmanifest' },
    
    // 主题颜色配置
    { name: 'msapplication-TileColor', content: '#ff9933' },
    { name: 'theme-color', content: '#ffffff' }
  ];

  // 2. 定义注入函数 (保持上一版的优化逻辑)
  function injectFavicons(data, basePath) {
    const head = document.head;
    const prefix = (basePath === '/') ? '/' : basePath;

    data.forEach(item => {
      let element;
      if (item.rel) {
        element = document.createElement('link');
        element.rel = item.rel;
        if (item.sizes) element.sizes = item.sizes;
        if (item.type) element.type = item.type;
        element.href = normalizePath(prefix + item.href); 
      } else if (item.name) {
        element = document.createElement('meta');
        element.name = item.name;
        element.content = item.content;
      }
      if (element) {
        head.appendChild(element);
      }
    });
  }
  // --- Favicon 模块结束 ---

  // ... (你之前版本的所有其他函数 getBasePath, loadHTML, etc. 保持不变) ...
  // --- 获取基础路径 (保持不变) ---
  function getBasePath() {
    const mainScript = document.getElementById('main-include-script');
    if (!mainScript) {
        console.error("Script with id 'main-include-script' not found.");
        return "/";
    }
    const scriptUrl = new URL(mainScript.src, window.location.href);
    let pathName = scriptUrl.pathname;
    const pathSuffix = "assets/js/includes.js";
    if (!pathName.endsWith(pathSuffix)) {
        console.warn("Unexpected includes.js path. Defaulting to root.");
        return "/";
    }
    return pathName.substring(0, pathName.lastIndexOf(pathSuffix));
  }
  // --- 加载 HTML 片段 (保持不变) ---
  function loadHTML(selector, filePath, basePath) {
    const element = document.querySelector(selector);
    if (element) {
        const fullPath = normalizePath(basePath + filePath);
        fetch(fullPath)
        .then(response => {
            if (!response.ok) throw new Error(`File not found: ${fullPath}`);
            return response.text();
        })
        .then(data => {
            element.innerHTML = data;
            if (selector === '#header-placeholder' || selector === '#footer-placeholder' || selector === '#sidebar-placeholder') {
            prefixLinksWithBasePath(basePath, selector);
            }
            if (selector === '#header-placeholder') {
            applyActiveClassByURL();
            initializeHeaderInteractions();
            }
        })
        .catch(error => console.error(`Error loading ${filePath}:`, error));
    }
  }
  // --- 为链接和图片加 basePath 前缀 (保持不变) ---
  function prefixLinksWithBasePath(basePath, contextSelector) {
    let processedPath = basePath;
    if (processedPath === '/') {
        processedPath = '';
    } else if (processedPath.endsWith('/')) {
        processedPath = processedPath.slice(0, -1);
    }
    if (processedPath === '') return;
    const contextElement = document.querySelector(contextSelector);
    if (!contextElement) return;
    contextElement.querySelectorAll('a').forEach(link => {
        const href = link.getAttribute('href');
        if (!href || href.startsWith('http') || href.startsWith('#') || href.startsWith('//') || href.startsWith('mailto:') || href.startsWith('tel:')) {
        return;
        }
        if (href.startsWith('/')) {
            link.setAttribute('href', processedPath + href);
        }
    });
    contextElement.querySelectorAll('img').forEach(img => {
        const src = img.getAttribute('src');
        if (!src || src.startsWith('http') || src.startsWith('//')) {
        return;
        }
        if (src.startsWith('/')) {
            img.setAttribute('src', processedPath + src);
        }
    });
  }
  // --- 高亮当前导航 (保持不变) ---
  function applyActiveClassByURL() {
    const currentPath = window.location.pathname.replace(/\/index\.html$/, '/').replace(/\/+$/, '/');
    const navLinks = document.querySelectorAll('nav a');
    let bestMatchLink = null;
    navLinks.forEach(link => {
        if (!link.href) return;
        try {
        const linkPath = new URL(link.href).pathname.replace(/\/index\.html$/, '/').replace(/\/+$/, '/');
        if (currentPath.startsWith(linkPath)) {
            if (!bestMatchLink || linkPath.length > new URL(bestMatchLink.href).pathname.length) {
            bestMatchLink = link;
            }
        }
        } catch (e) {
        console.error("Could not parse link href:", link.href, e);
        }
    });
    navLinks.forEach(link => {
        link.classList.remove('active');
        link.classList.remove('active-parent');
    });
    if (bestMatchLink) {
        bestMatchLink.classList.add('active');
        const parentDropdown = bestMatchLink.closest('.has-dropdown');
        if (parentDropdown) {
        const parentLink = parentDropdown.querySelector('a');
        if (parentLink) {
            parentLink.classList.add('active-parent');
        }
        }
    }
  }
  // --- 格式化路径 (保持不变) ---
  function normalizePath(path) {
    return path.replace(/([^:])(\/\/+)/g, '$1/');
  }
  // --- 初始化导航交互 (保持不变) ---
  function initializeHeaderInteractions() {
    const hamburger = document.getElementById('hamburger-menu');
    const body = document.body;
    if (hamburger) {
        hamburger.addEventListener('click', () => body.classList.toggle('nav-open'));
    }
    const dropdownToggles = document.querySelectorAll('.has-dropdown > a');
    const isMobileView = () => window.innerWidth <= 768;
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
        if (isMobileView()) {
            e.preventDefault();
            this.parentElement.classList.toggle('menu-open');
        }
        });
    });
  }
  // --- 获取应加载的 Sidebar 文件名 (保持不变) ---
  function getSidebarFile() {
    const path = window.location.pathname.toLowerCase();
    const metaSidebar = document.querySelector('meta[name="sidebar-type"]');
    if (metaSidebar) {
        const type = metaSidebar.content.trim().toLowerCase();
        if (type === 'applications') return '_sidebar-applications.html';
        if (type === 'blog') return '_sidebar-blog.html';
    }
    if (path.includes('/applications/') || path.includes('/agv-') || path.includes('/crossbelt-') || path.includes('/wedge-lock-')) {
        return '_sidebar-applications.html';
    }
    if (path.includes('/blog/')) {
        return '_sidebar-blog.html';
    }
    return '_sidebar.html';
  }
  
  // --- 页面加载执行入口 (保持不变) ---
  document.addEventListener("DOMContentLoaded", function () {
    const basePath = getBasePath();

    // ✅ 第一步: 立刻注入与你文件匹配的“门派徽章”
    injectFavicons(faviconData, basePath);

    // ✅ 第二步: 继续执行你原来的加载任务
    loadHTML('#header-placeholder', '_header.html', basePath);
    loadHTML('#footer-placeholder', '_footer.html', basePath);
    loadHTML('#sidebar-placeholder', getSidebarFile(), basePath);
    loadHTML('#author-bio-placeholder', '_author-bio.html', basePath);
  });

})(); // 立即执行函数结束