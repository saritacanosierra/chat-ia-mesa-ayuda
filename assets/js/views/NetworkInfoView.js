/**
 * Vista: Información de la red
 * Maneja la visualización de la información de la red
 */
class NetworkInfoView {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
    }

    /**
     * Muestra la información de la red
     * @param {Object} data - Datos de la red
     */
    displayNetworkInfo(data) {
        const featuresList = data.features.map(feature => 
            `<li>${Utils.sanitize(feature)}</li>`
        ).join('');

        this.container.innerHTML = `
            <h3>
                ${Utils.sanitize(data.network_name)}
                <span class="network-status ${data.status}">${Utils.sanitize(data.status)}</span>
            </h3>
            <p>${Utils.sanitize(data.description)}</p>
            <ul>${featuresList}</ul>
        `;
    }

    /**
     * Muestra un estado de carga
     */
    displayLoading() {
        this.container.innerHTML = '<div class="loading">Cargando información...</div>';
    }
}

