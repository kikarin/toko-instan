import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useIndoRegions } from '@/lib/useIndoRegions';
import { onMounted, watch } from 'vue';

const { provinces, cities, loadProvinces, loadCities } = useIndoRegions();

onMounted(() => {
    loadProvinces();
});

watch([() => form.province, provinces], ([newProvName, provs], [oldProvName]) => {
    const prov = provs.find(p => p.name === newProvName);
    if (prov) {
        loadCities(prov.id);
    } else {
        cities.value = [];
    }
    
    // If the province was changed by user interaction (not initial load)
    if (oldProvName && oldProvName !== newProvName) {
        form.city = '';
    }
});
