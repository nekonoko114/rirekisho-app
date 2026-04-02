// Import app bootstrap (Bootstrap JS can be imported via npm)
import "./bootstrap";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import { Chart, registerables } from "chart.js";
Chart.register(...registerables);
window.Chart = Chart;

// Import PDF download functionality
import "./pdf-download";

// Import era year validation
import "./era-validation";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();
