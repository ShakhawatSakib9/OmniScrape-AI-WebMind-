const { chromium } = require('playwright');
const cheerio = require('cheerio');
const fs = require('fs');

async function run() {
  const mode = process.argv[2] || 'fetch-dom';
  const inputArg = process.argv[3] || '{}';

  let config = {};
  try {
    if (fs.existsSync(inputArg)) {
      config = JSON.parse(fs.readFileSync(inputArg, 'utf8'));
    } else {
      config = JSON.parse(inputArg);
    }
  } catch (e) {
    config = { url: inputArg };
  }

  const startTime = Date.now();
  let browser = null;

  try {
    browser = await chromium.launch({
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-blink-features=AutomationControlled'
      ]
    });

    const contextOptions = {
      userAgent: config.user_agent || 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
      viewport: { width: 1366 + Math.floor(Math.random() * 50), height: 768 + Math.floor(Math.random() * 30) }
    };

    if (config.proxy && config.proxy.server) {
      contextOptions.proxy = {
        server: config.proxy.server,
        username: config.proxy.username || undefined,
        password: config.proxy.password || undefined
      };
    }

    const context = await browser.newContext(contextOptions);

    const page = await context.newPage();
    page.setDefaultTimeout(config.timeout || 30000);

    const targetUrl = config.url;
    if (!targetUrl) {
      throw new Error('Target URL is required');
    }

    // Handle Pre-Extraction Authentication if configured
    if (config.auth_type === 'form_login' && config.auth_config) {
      try {
        const auth = config.auth_config;
        if (auth.login_url && auth.username_selector && auth.password_selector) {
          await page.goto(auth.login_url, { waitUntil: 'domcontentloaded' });
          if (auth.username) await page.fill(auth.username_selector, auth.username);
          if (auth.password) await page.fill(auth.password_selector, auth.password);
          if (auth.submit_selector) {
            await Promise.all([
              page.waitForNavigation({ timeout: 15000 }).catch(() => {}),
              page.click(auth.submit_selector)
            ]);
          }
          await page.waitForTimeout(1000);
        }
      } catch (authErr) {
        // Continue and attempt target page even if login flow threw error
      }
    } else if (config.auth_type === 'cookies' && config.session_cookies) {
      try {
        const cookies = typeof config.session_cookies === 'string' ? JSON.parse(config.session_cookies) : config.session_cookies;
        await context.addCookies(cookies);
      } catch (_) {}
    }

    // Navigate to page
    await page.goto(targetUrl, { waitUntil: 'domcontentloaded' });
    
    // Quick auto-scroll to trigger lazy loading
    await autoScroll(page);

    // Wait a brief moment for dynamic rendering
    await page.waitForTimeout(1500);

    const title = await page.title();
    const currentUrl = page.url();

    // Mode 1: Fetch DOM for AI Schema Inference
    if (mode === 'fetch-dom') {
      const fullHtml = await page.content();
      const minifiedHtml = minifyDom(fullHtml);
      
      const result = {
        success: true,
        title,
        url: currentUrl,
        raw_length: fullHtml.length,
        minified_length: minifiedHtml.length,
        html: minifiedHtml,
        execution_time_ms: Date.now() - startTime
      };

      console.log(JSON.stringify(result));
      await browser.close();
      return;
    }

    // Mode 2: Extract structured data with selectors
    if (mode === 'extract-data') {
      const records = [];
      let currentPage = 1;
      const maxPages = config.max_pages || 1;
      const containerSelector = config.container_selector || null;
      const selectors = config.selectors || {}; // { field_name: { selector, attr, type } }

      while (currentPage <= maxPages) {
        const html = await page.content();
        const $ = cheerio.load(html);

        if (containerSelector && $(containerSelector).length > 0) {
          $(containerSelector).each((i, el) => {
            const row = {};
            let hasAnyData = false;

            for (const [fieldName, selConfig] of Object.entries(selectors)) {
              const sel = selConfig.selector;
              const attr = selConfig.attr || 'text';
              const targetEl = $(el).find(sel);

              let val = null;
              if (targetEl.length > 0) {
                if (attr === 'text') {
                  val = targetEl.first().text().replace(/\s+/g, ' ').trim();
                } else if (attr === 'href' || attr === 'src' || attr === 'value') {
                  val = targetEl.first().attr(attr) || null;
                  if (val && (attr === 'href' || attr === 'src') && !val.startsWith('http') && !val.startsWith('data:')) {
                    try { val = new URL(val, currentUrl).href; } catch (_) {}
                  }
                } else if (attr === 'inner_html') {
                  val = targetEl.first().html() || null;
                }
              }

              // Normalization based on field_type
              if (val && selConfig.field_type === 'price') {
                const numericMatch = val.replace(/,/g, '').match(/\d+(\.\d+)?/);
                val = numericMatch ? parseFloat(numericMatch[0]) : val;
              } else if (val && selConfig.field_type === 'number') {
                const num = parseFloat(val.replace(/[^\d.-]/g, ''));
                val = isNaN(num) ? val : num;
              }

              if (val !== null && val !== '') {
                hasAnyData = true;
              }
              row[fieldName] = val;
            }

            if (hasAnyData) {
              records.push(row);
            }
          });
        } else {
          // No container - individual global selectors
          const row = {};
          let hasAnyData = false;
          for (const [fieldName, selConfig] of Object.entries(selectors)) {
            const sel = selConfig.selector;
            const attr = selConfig.attr || 'text';
            const targetEl = $(sel);

            let val = null;
            if (targetEl.length > 0) {
              if (attr === 'text') {
                val = targetEl.first().text().replace(/\s+/g, ' ').trim();
              } else if (attr === 'href' || attr === 'src') {
                val = targetEl.first().attr(attr) || null;
              }
            }
            if (val !== null && val !== '') hasAnyData = true;
            row[fieldName] = val;
          }
          if (hasAnyData) records.push(row);
        }

        // Check if there is next page button
        if (currentPage < maxPages && config.pagination_type === 'next_button' && config.pagination_selector) {
          try {
            const nextBtn = await page.$(config.pagination_selector);
            if (nextBtn) {
              await nextBtn.click();
              await page.waitForTimeout(2000);
              await autoScroll(page);
              currentPage++;
            } else {
              break;
            }
          } catch (e) {
            break;
          }
        } else {
          break;
        }
      }

      const result = {
        success: true,
        title,
        url: currentUrl,
        records_count: records.length,
        records,
        execution_time_ms: Date.now() - startTime
      };

      console.log(JSON.stringify(result));
      await browser.close();
      return;
    }

    // Mode 3: Test Selector Candidates (for Self-Healing)
    if (mode === 'test-selector') {
      const html = await page.content();
      const $ = cheerio.load(html);
      const candidates = config.candidates || []; // list of selector strings
      const container = config.container_selector || null;

      const evaluations = candidates.map(sel => {
        let matchCount = 0;
        const samples = [];

        if (container && $(container).length > 0) {
          $(container).each((i, el) => {
            if (i >= 5) return false;
            const target = $(el).find(sel);
            if (target.length > 0) {
              matchCount++;
              samples.push(target.first().text().replace(/\s+/g, ' ').trim());
            }
          });
        } else {
          $(sel).each((i, el) => {
            if (i >= 5) return false;
            matchCount++;
            samples.push($(el).text().replace(/\s+/g, ' ').trim());
          });
        }

        const confidence = matchCount > 0 ? Math.min(1.0, (matchCount / Math.min(5, container ? $(container).length : 5))) : 0.0;

        return {
          selector: sel,
          match_count: matchCount,
          samples,
          confidence_score: parseFloat(confidence.toFixed(2))
        };
      });

      console.log(JSON.stringify({
        success: true,
        evaluations,
        execution_time_ms: Date.now() - startTime
      }));
      await browser.close();
      return;
    }

    // Mode 4: Fetch Proxy DOM for Visual Picker (Keeps styles, fixes relative links)
    if (mode === 'fetch-proxy-dom') {
      const fullHtml = await page.content();
      
      // Inject base tag so relative CSS/Images load correctly in the iframe
      const proxyHtml = fullHtml.replace(
        /<head([^>]*)>/i, 
        `<head$1>\n  <base href="${currentUrl}">\n`
      );

      console.log(JSON.stringify({
        success: true,
        title,
        url: currentUrl,
        html: proxyHtml,
        execution_time_ms: Date.now() - startTime
      }));
      await browser.close();
      return;
    }

    throw new Error(`Unknown mode: ${mode}`);

  } catch (err) {
    if (browser) await browser.close();
    console.log(JSON.stringify({
      success: false,
      error: err.message,
      execution_time_ms: Date.now() - startTime
    }));
    process.exit(1);
  }
}

async function autoScroll(page) {
  try {
    await page.evaluate(async () => {
      await new Promise((resolve) => {
        let totalHeight = 0;
        const distance = 300;
        const timer = setInterval(() => {
          const scrollHeight = document.body.scrollHeight;
          window.scrollBy(0, distance);
          totalHeight += distance;

          if (totalHeight >= 1200 || totalHeight >= scrollHeight) {
            clearInterval(timer);
            resolve();
          }
        }, 100);
      });
    });
  } catch (_) {}
}

function minifyDom(rawHtml) {
  const $ = cheerio.load(rawHtml);
  
  // Strip non-semantic noise to save 85%+ LLM tokens
  $('script').remove();
  $('style').remove();
  $('svg').remove();
  $('noscript').remove();
  $('iframe').remove();
  $('link').remove();
  $('meta').remove();
  $('header').remove();
  $('footer').remove();
  $('nav').remove();

  // Strip inline styles, data attributes, tracking pixels
  $('*').each((i, el) => {
    const attribs = el.attribs || {};
    for (const key of Object.keys(attribs)) {
      if (key.startsWith('data-') || key.startsWith('aria-') || key === 'style' || key === 'onclick' || key.startsWith('on')) {
        delete attribs[key];
      }
    }
  });

  return $('body').html() || '';
}

run();