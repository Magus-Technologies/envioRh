const API_URL = import.meta.env.VITE_APISPERU_URL;
const API_TOKEN = import.meta.env.VITE_APISPERU_TOKEN;

export const consultarDNI = async (dni) => {
    try {
        const response = await fetch(`${API_URL}/dni/${dni}?token=${API_TOKEN}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) throw new Error('DNI no encontrado');
        const data = await response.json();
        return {
            success: true,
            data: {
                nombres: data.nombres,
                apellidoPaterno: data.apellidoPaterno,
                apellidoMaterno: data.apellidoMaterno,
                nombreCompleto: `${data.nombres} ${data.apellidoPaterno} ${data.apellidoMaterno}`.trim(),
            },
        };
    } catch (error) {
        return { success: false, message: error.message || 'Error al consultar DNI' };
    }
};

export const consultarRUC = async (ruc) => {
    try {
        const response = await fetch(`${API_URL}/ruc/${ruc}?token=${API_TOKEN}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) throw new Error('RUC no encontrado');
        const data = await response.json();
        return {
            success: true,
            data: {
                razonSocial: data.razonSocial,
                nombreComercial: data.nombreComercial || data.razonSocial,
                direccion: data.direccion,
                departamento: data.departamento,
                provincia: data.provincia,
                distrito: data.distrito,
                ubigeo: data.ubigeo,
                estado: data.estado,
                condicion: data.condicion,
            },
        };
    } catch (error) {
        return { success: false, message: error.message || 'Error al consultar RUC' };
    }
};

export const consultarDocumento = async (documento) => {
    const docLimpio = (documento || '').trim();
    if (docLimpio.length === 8) return await consultarDNI(docLimpio);
    if (docLimpio.length === 11) return await consultarRUC(docLimpio);
    return { success: false, message: 'Documento inválido. DNI (8) o RUC (11) dígitos.' };
};
