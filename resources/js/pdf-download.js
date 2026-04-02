import jsPDF from "jspdf";
import html2canvas from "html2canvas";

/**
 * Generate PDF from resume HTML and download
 */
window.downloadResumePDF = async function () {
    const button = document.querySelector(".pdf-download-btn");
    const originalText = button ? button.textContent : "";

    try {
        if (button) {
            button.disabled = true;
            button.textContent = "PDF生成中...";
        }

        // Get both pages (left and right)
        const leftPage = document.querySelector(".page-left");
        const rightPage = document.querySelector(".page-right");

        if (!leftPage || !rightPage) {
            throw new Error("Resume pages not found");
        }

        // Canvas options for better quality
        const canvasOptions = {
            scale: 3,
            useCORS: true,
            logging: false,
            backgroundColor: "#ffffff",
            windowWidth: 1200,
            allowTaint: true,
        };

        // Capture both pages separately
        const leftCanvas = await html2canvas(leftPage, canvasOptions);
        const rightCanvas = await html2canvas(rightPage, canvasOptions);

        // Create PDF in landscape A4 format with minimal margins
        const pdf = new jsPDF("l", "mm", "a4");
        const pdfWidth = 297; // A4 landscape width
        const pdfHeight = 210; // A4 landscape height

        // Use minimal margins (2mm on each side)
        const margin = 2;
        const usableWidth = pdfWidth - margin * 2;
        const usableHeight = pdfHeight - margin * 2;

        // Calculate dimensions to fit both pages side by side
        const totalWidth = leftCanvas.width + rightCanvas.width;
        const maxHeight = Math.max(leftCanvas.height, rightCanvas.height);

        // Scale to fit within usable area
        const widthScale = usableWidth / (totalWidth / canvasOptions.scale);
        const heightScale = usableHeight / (maxHeight / canvasOptions.scale);
        const scale = Math.min(widthScale, heightScale);

        const finalWidth = (totalWidth / canvasOptions.scale) * scale;
        const finalHeight = (maxHeight / canvasOptions.scale) * scale;

        // Center the content if there's extra space
        const xOffset = margin + (usableWidth - finalWidth) / 2;
        const yOffset = margin + (usableHeight - finalHeight) / 2;

        const leftWidth = (leftCanvas.width / canvasOptions.scale) * scale;
        const rightWidth = (rightCanvas.width / canvasOptions.scale) * scale;

        // Add left page
        const leftImgData = leftCanvas.toDataURL("image/jpeg", 0.98);
        pdf.addImage(
            leftImgData,
            "JPEG",
            xOffset,
            yOffset,
            leftWidth,
            finalHeight
        );

        // Add right page
        const rightImgData = rightCanvas.toDataURL("image/jpeg", 0.98);
        pdf.addImage(
            rightImgData,
            "JPEG",
            xOffset + leftWidth,
            yOffset,
            rightWidth,
            finalHeight
        );

        // Generate filename
        const filename = `resume-${Date.now()}.pdf`;

        // Download
        pdf.save(filename);

        if (button) {
            button.textContent = "ダウンロード完了！";
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
            }, 2000);
        }
    } catch (error) {
        console.error("PDF generation error:", error);
        alert(
            "PDF生成に失敗しました。ブラウザの印刷機能（Cmd+P / Ctrl+P）をお試しください。"
        );

        if (button) {
            button.textContent = originalText;
            button.disabled = false;
        }
    }
};
