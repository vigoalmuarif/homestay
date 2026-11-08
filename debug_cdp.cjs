const { spawn } = require('child_process');
const http = require('http');

async function main() {
    const chrome = spawn('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', [
        '--headless=new',
        '--remote-debugging-port=9222',
        '--user-data-dir=C:\\Windows\\Temp\\cdp_profile_' + Date.now(),
        '--disable-gpu',
        '--no-sandbox',
        'about:blank'
    ]);

    // Poll until port 9222 is ready
    let target = null;
    for (let i = 0; i < 20; i++) {
        await new Promise(r => setTimeout(r, 250));
        try {
            const list = await fetch('http://127.0.0.1:9222/json/new').then(r => r.json());
            if (list && list.webSocketDebuggerUrl) {
                target = list;
                break;
            }
        } catch (e) {}
    }

    if (!target) {
        console.error('Failed to connect to Chrome CDP');
        chrome.kill();
        return;
    }

    console.log('Connected to target:', target.webSocketDebuggerUrl);

    const ws = new WebSocket(target.webSocketDebuggerUrl);

    let msgId = 1;
    const callbacks = new Map();

    function send(method, params = {}) {
        return new Promise((resolve, reject) => {
            const id = msgId++;
            callbacks.set(id, { resolve, reject });
            ws.send(JSON.stringify({ id, method, params }));
        });
    }

    ws.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        if (msg.id && callbacks.has(msg.id)) {
            const { resolve } = callbacks.get(msg.id);
            callbacks.delete(msg.id);
            resolve(msg.result);
        } else if (msg.method === 'Runtime.consoleAPICalled') {
            console.log('[BROWSER CONSOLE]', msg.params.type, msg.params.args.map(a => a.value || a.description).join(' '));
        } else if (msg.method === 'Runtime.exceptionThrown') {
            console.error('[BROWSER EXCEPTION]', msg.params.exceptionDetails.text, msg.params.exceptionDetails.exception?.description || '');
        }
    };

    await new Promise(r => ws.onopen = r);
    await send('Runtime.enable');
    await send('Page.enable');
    await send('DOM.enable');

    console.log('Navigating to login...');
    await send('Page.navigate', { url: 'http://localhost:8000/login' });
    await new Promise(r => setTimeout(r, 1500));

    // Fill in credentials and submit form
    await send('Runtime.evaluate', {
        expression: `
            document.querySelector('input[name="email"]').value = 'admin@homestay.test';
            document.querySelector('input[name="password"]').value = 'password';
            document.querySelector('form').submit();
        `
    });

    console.log('Submitted login, waiting for dashboard redirect...');
    await new Promise(r => setTimeout(r, 2000));

    console.log('Navigating to /service-inclusions...');
    await send('Page.navigate', { url: 'http://localhost:8000/service-inclusions' });
    await new Promise(r => setTimeout(r, 2000));

    // Click "+ Tambah Hak Inklusi Paket"
    console.log('Clicking "+ Tambah Hak Inklusi Paket"...');
    const evalRes = await send('Runtime.evaluate', {
        expression: `
            (() => {
                const btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('Tambah Hak Inklusi'));
                if (btn) {
                    btn.click();
                    return 'Clicked button: ' + btn.textContent.trim();
                }
                return 'Button not found';
            })()
        `,
        returnByValue: true
    });
    console.log('Click result:', evalRes.result?.value);

    // Wait 500ms for modal to open and select2 to initialize
    await new Promise(r => setTimeout(r, 800));

    // Inspect the DOM of the select2 container
    const inspectRes = await send('Runtime.evaluate', {
        expression: `
            (() => {
                const modal = document.querySelector('#inclusion-modal-container');
                const modalVisible = modal ? getComputedStyle(modal).display : 'no modal';
                const select = document.querySelector('#select2-service-item');
                const selectContainer = document.querySelector('.select2-container');
                const jqueryExists = typeof window.jQuery !== 'undefined';
                const select2FnExists = jqueryExists && typeof window.jQuery.fn.select2 !== 'undefined';
                const select2Data = select && jqueryExists ? !!window.jQuery(select).data('select2') : false;

                return {
                    modalDisplay: modalVisible,
                    jqueryExists: jqueryExists,
                    select2FnExists: select2FnExists,
                    hasSelect2Container: !!selectContainer,
                    select2Data: select2Data,
                    selectOuterHtml: select ? select.outerHTML.substring(0, 300) : 'none',
                    selectContainerHtml: selectContainer ? selectContainer.outerHTML.substring(0, 300) : 'none',
                    selectContainerStyle: selectContainer ? {
                        display: getComputedStyle(selectContainer).display,
                        width: getComputedStyle(selectContainer).width,
                        height: getComputedStyle(selectContainer).height
                    } : null
                };
            })()
        `,
        returnByValue: true
    });

    console.log('INSPECTION RESULT:', JSON.stringify(inspectRes.result?.value, null, 2));

    ws.close();
    chrome.kill();
    process.exit(0);
}

main().catch(err => {
    console.error('Fatal error:', err);
    process.exit(1);
});

