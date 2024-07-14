// fingerprint.js

function getFingerprint() {
    const fingerprint = {
        userAgent: navigator.userAgent,
        platform: navigator.platform,
        languages: navigator.languages,
        screenResolution: `${screen.width}x${screen.height}`,
        colorDepth: screen.colorDepth,
        timezoneOffset: new Date().getTimezoneOffset(),
        plugins: getPlugins(),
        canvasFingerprint: getCanvasFingerprint()
    };

    return JSON.stringify(fingerprint);
}

function getPlugins() {
    if (!navigator.plugins) {
        return [];
    }
    const plugins = [];
    for (let i = 0; i < navigator.plugins.length; i++) {
        plugins.push(navigator.plugins[i].name);
    }
    return plugins;
}

function getCanvasFingerprint() {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    ctx.textBaseline = 'top';
    ctx.font = '14px Arial';
    ctx.textBaseline = 'alphabetic';
    ctx.fillStyle = '#f60';
    ctx.fillRect(125, 1, 62, 20);
    ctx.fillStyle = '#069';
    ctx.fillText('Browser Fingerprint', 2, 15);
    ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
    ctx.fillText('Browser Fingerprint', 4, 17);
    return canvas.toDataURL();
}
