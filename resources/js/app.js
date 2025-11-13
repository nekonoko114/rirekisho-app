// Import app bootstrap (Bootstrap JS can be imported via npm)
import "./bootstrap";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import { Chart, registerables } from "chart.js";
Chart.register(...registerables);
window.Chart = Chart;

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();
