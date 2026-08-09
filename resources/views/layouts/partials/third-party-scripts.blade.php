@if (!app()->environment('local'))
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }

    const loadThirdPartyScripts = () => {
      const scripts = [
        {
          src: 'https://www.googletagmanager.com/gtag/js?id=G-Q4QNW9JPGG',
          async: true,
        },
        {
          src: 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7042088525718719',
          async: true,
          crossorigin: 'anonymous',
        },
      ];

      scripts.forEach(({ src, async, crossorigin }) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = async;

        if (crossorigin) {
          script.crossOrigin = crossorigin;
        }

        document.head.appendChild(script);
      });

      gtag('js', new Date());
      gtag('config', 'G-Q4QNW9JPGG');
    };

    const scheduleThirdPartyScripts = () => {
      if ('requestIdleCallback' in window) {
        requestIdleCallback(loadThirdPartyScripts, { timeout: 3000 });
        return;
      }

      setTimeout(loadThirdPartyScripts, 1500);
    };

    if (document.readyState === 'complete') {
      scheduleThirdPartyScripts();
    } else {
      window.addEventListener('load', scheduleThirdPartyScripts, { once: true });
    }
  </script>
@endif
