// ajax.js - small helper for Fetch with CSRF and JSON handling
export async function postJSON(url, data){
    const meta = document.querySelector('meta[name="csrf-token"]');
    const csrf = meta ? meta.getAttribute('content') : null;
    const headers = {'Content-Type':'application/json','Accept':'application/json'};
    if (csrf) headers['X-CSRF-Token'] = csrf;

    const res = await fetch(url, {method:'POST', headers, body: JSON.stringify(data)});
    const json = await res.json().catch(()=>null);
    
    // If CSRF error, try to refresh token and retry once
    if (res.status === 403 && json && json.errors && json.errors.csrf) {
        await refreshCsrfToken();
        const newCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (newCsrf) {
            headers['X-CSRF-Token'] = newCsrf;
            const retryRes = await fetch(url, {method:'POST', headers, body: JSON.stringify(data)});
            const retryJson = await retryRes.json().catch(()=>null);
            return {status: retryRes.status, ok: retryRes.ok, data: retryJson};
        }
    }
    
    return {status: res.status, ok: res.ok, data: json};
}

async function refreshCsrfToken(){
    try {
        const res = await fetch('index.php?route=dashboard', {headers:{'Accept':'text/html'}});
        const text = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, 'text/html');
        const newToken = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (newToken) {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', newToken);
        }
    } catch(e) {
        console.error('Failed to refresh CSRF token', e);
    }
}
