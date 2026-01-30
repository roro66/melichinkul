/**
 * Configuración estándar para DataTables en toda la aplicación Melichinkul
 * 
 * CARACTERÍSTICAS:
 * - Botones de exportación (Excel, CSV) que exportan TODAS las filas del servidor
 * - Selector de columnas visibles
 * - Modo oscuro automático
 * - Idioma español
 * - Server-side processing
 * - Responsive
 */
window.initDataTable = function(tableId, options = {}) {
    const defaultOptions = {
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        },
        dom: "Bflrtip",
        buttons: [
            {
                extend: "colvis",
                text: "<i class=\"fas fa-columns\"></i> Columnas",
                className: "btn-colvis",
                columns: ":not(.no-toggle)"
            },
            {
                text: "<i class=\"fas fa-file-excel\"></i> Excel",
                className: "btn-excel",
                action: function(e, dt, node, config) {
                    exportToServer(dt, "excel");
                }
            },
            {
                text: "<i class=\"fas fa-file-csv\"></i> CSV",
                className: "btn-csv",
                action: function(e, dt, node, config) {
                    exportToServer(dt, "csv");
                }
            },
            {
                extend: "print",
                text: "<i class=\"fas fa-print\"></i> Imprimir",
                className: "btn-print",
                exportOptions: {
                    columns: ":visible:not(.no-export)"
                }
            }
        ],
        drawCallback: function() {
            applyDarkModeStyles();
        }
    };

    const finalOptions = { ...defaultOptions, ...options };
    
    if (options.buttons) {
        finalOptions.buttons = [...defaultOptions.buttons, ...options.buttons];
    }

    const table = $("#" + tableId).DataTable(finalOptions);

    const observer = new MutationObserver(function() {
        applyDarkModeStyles();
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"]
    });

    return table;
};

function applyDarkModeStyles() {
    const isDark = document.documentElement.classList.contains("dark");
    
    if (isDark) {
        $(".dt-buttons button").addClass("dark-mode-btn");
        $(".dt-button-collection").addClass("dark-mode-dropdown");
    } else {
        $(".dt-buttons button").removeClass("dark-mode-btn");
        $(".dt-button-collection").removeClass("dark-mode-dropdown");
    }
}

window.exportToServer = function(dt, format) {
    const tableId = dt.table().node().id;
    const table = dt;
    const searchValue = table.search();
    
    const filters = {
        search: searchValue || "",
        _token: document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content")
    };
    
    let exportRoute = "";
    const entityMap = {
        "vehicles-table": "/vehiculos/export/",
        "maintenances-table": "/mantenimientos/export/",
        "drivers-table": "/conductores/export/",
        "certifications-table": "/certificaciones/export/",
        "purchases-table": "/compras/export/"
    };
    
    if (entityMap[tableId]) {
        exportRoute = entityMap[tableId] + format;
    } else {
        const entity = tableId.replace("-table", "");
        exportRoute = "/" + entity + "/export/" + format;
    }
    
    const form = document.createElement("form");
    form.method = "POST";
    form.action = exportRoute;
    form.style.display = "none";
    
    Object.keys(filters).forEach(function(key) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = filters[key] || "";
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};
