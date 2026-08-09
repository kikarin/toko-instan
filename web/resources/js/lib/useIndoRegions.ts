import { ref, watch } from 'vue';

export interface Region {
    id: string;
    name: string;
}

export function useIndoRegions() {
    const provinces = ref<Region[]>([]);
    const cities = ref<Region[]>([]);
    const districts = ref<Region[]>([]);
    const isLoadingProvinces = ref(false);
    const isLoadingCities = ref(false);
    const isLoadingDistricts = ref(false);

    async function loadProvinces() {
        isLoadingProvinces.value = true;
        try {
            const res = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
            provinces.value = await res.json();
        } catch (e) {
            console.error('Failed to load provinces', e);
        } finally {
            isLoadingProvinces.value = false;
        }
    }

    async function loadCities(provinceId: string) {
        isLoadingCities.value = true;
        try {
            const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`);
            cities.value = await res.json();
        } catch (e) {
            console.error('Failed to load cities', e);
        } finally {
            isLoadingCities.value = false;
        }
    }

    async function loadDistricts(cityId: string) {
        isLoadingDistricts.value = true;
        try {
            const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${cityId}.json`);
            districts.value = await res.json();
        } catch (e) {
            console.error('Failed to load districts', e);
        } finally {
            isLoadingDistricts.value = false;
        }
    }

    return {
        provinces,
        cities,
        districts,
        isLoadingProvinces,
        isLoadingCities,
        isLoadingDistricts,
        loadProvinces,
        loadCities,
        loadDistricts
    };
}
