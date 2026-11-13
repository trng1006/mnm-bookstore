import './bootstrap';
import QRCode from 'qrcode';

// Render chuỗi VietQR từ payOS thành ảnh QR trực tiếp trên giao diện
window.renderVietQR = async function (imgElementId, qrCodeText) {
    const imgEl = document.getElementById(imgElementId);
    if (!imgEl || !qrCodeText) {
        return;
    }

    try {
        const dataUrl = await QRCode.toDataURL(qrCodeText, {
            width: 280,
            margin: 2,
            color: {
                dark: '#1A202C',
                light: '#FFFFFF',
            },
        });
        imgEl.src = dataUrl;
    } catch (error) {
        console.error('Không thể tạo mã QR:', error);
    }
};
