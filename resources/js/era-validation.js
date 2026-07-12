/**
 * 年号の年数範囲制限
 * 昭和: 1-64年
 * 平成: 1-31年
 * 令和: 1-60年
 */

const eraYearRanges = {
    昭和: { min: 1, max: 64 },
    平成: { min: 1, max: 31 },
    令和: { min: 1, max: 60 },
};

function updateEraYearOptions(eraSelect, yearSelect) {
    const selectedEra = eraSelect.value;
    const currentValue = yearSelect.value;

    if (!selectedEra || !eraYearRanges[selectedEra]) {
        return;
    }

    const range = eraYearRanges[selectedEra];

    // 既存のオプションをクリア（最初の「年」オプション以外）
    while (yearSelect.options.length > 1) {
        yearSelect.remove(1);
    }

    // 新しい範囲でオプションを追加
    for (let year = range.min; year <= range.max; year++) {
        const option = document.createElement("option");
        option.value = year;
        option.textContent = year;
        if (year == currentValue) {
            option.selected = true;
        }
        yearSelect.appendChild(option);
    }
}

// DOMContentLoadedイベントで初期化
document.addEventListener("DOMContentLoaded", function () {
    // すべての年号セレクトボックスを取得
    const eraSelects = document.querySelectorAll(".era-select");

    eraSelects.forEach((eraSelect) => {
        // 対応する年セレクトボックスを探す
        const yearSelect =
            eraSelect.parentElement.querySelector(".era-year-select");

        if (!yearSelect) return;

        // 初期状態で範囲を設定
        if (eraSelect.value) {
            updateEraYearOptions(eraSelect, yearSelect);
        }

        // 年号が変更されたときに年の範囲を更新
        eraSelect.addEventListener("change", function () {
            updateEraYearOptions(this, yearSelect);
        });
    });
});
