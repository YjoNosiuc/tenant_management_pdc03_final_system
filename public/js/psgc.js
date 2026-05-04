document.addEventListener('alpine:init', () => {
    Alpine.data('psgcAddress', (config = {}) => ({
        provinces: [],
        cities: [],
        barangays: [],
        selectedProvince: config.province || '',
        selectedCity: config.city || '',
        selectedBarangay: config.barangay || '',
        addressLine1: config.address_line1 || '',
        addressLine2: config.address_line2 || '',
        selectedProvinceCode: '',
        selectedCityCode: '',
        loading: false,
        citiesLoading: false,
        barangaysLoading: false,

        async init() {
            await this.loadProvinces();
        },

        async hydrateFromRow(row) {
            if (!row) {
                return;
            }
            this.selectedProvince = row.province || '';
            this.selectedCity = row.city || '';
            this.selectedBarangay = row.barangay || '';
            this.addressLine1 = row.address_line1 || '';
            this.addressLine2 = row.address_line2 || '';
            this.selectedProvinceCode = '';
            this.selectedCityCode = '';
            this.cities = [];
            this.barangays = [];
            await this.loadProvinces();
        },

        async loadProvinces() {
            this.loading = true;
            try {
                const res = await fetch('https://psgc.gitlab.io/api/provinces/');
                const data = await res.json();
                this.provinces = data.sort((a, b) => a.name.localeCompare(b.name));

                if (this.selectedProvince) {
                    const found = this.provinces.find(
                        (p) => p.name.toLowerCase() === this.selectedProvince.toLowerCase(),
                    );
                    if (found) {
                        this.selectedProvinceCode = found.code;
                        await this.loadCities(found.code);
                    }
                }
            } catch (e) {
                console.error('PSGC: Failed to load provinces', e);
            }
            this.loading = false;
        },

        async onProvinceChange(event) {
            const selected = this.provinces.find((p) => p.name === event.target.value);
            if (!selected) {
                this.selectedProvince = '';
                this.selectedProvinceCode = '';
                this.selectedCity = '';
                this.selectedCityCode = '';
                this.selectedBarangay = '';
                this.cities = [];
                this.barangays = [];
                return;
            }
            this.selectedProvince = selected.name;
            this.selectedProvinceCode = selected.code;
            this.selectedCity = '';
            this.selectedCityCode = '';
            this.selectedBarangay = '';
            this.cities = [];
            this.barangays = [];
            await this.loadCities(selected.code);
        },

        async loadCities(provinceCode) {
            this.citiesLoading = true;
            try {
                const res = await fetch(
                    `https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`,
                );
                const data = await res.json();
                this.cities = data.sort((a, b) => a.name.localeCompare(b.name));

                if (this.selectedCity) {
                    const found = this.cities.find(
                        (c) => c.name.toLowerCase() === this.selectedCity.toLowerCase(),
                    );
                    if (found) {
                        this.selectedCityCode = found.code;
                        await this.loadBarangays(found.code);
                    }
                }
            } catch (e) {
                console.error('PSGC: Failed to load cities', e);
            }
            this.citiesLoading = false;
        },

        async onCityChange(event) {
            const selected = this.cities.find((c) => c.name === event.target.value);
            if (!selected) {
                this.selectedCity = '';
                this.selectedCityCode = '';
                this.selectedBarangay = '';
                this.barangays = [];
                return;
            }
            this.selectedCity = selected.name;
            this.selectedCityCode = selected.code;
            this.selectedBarangay = '';
            this.barangays = [];
            await this.loadBarangays(selected.code);
        },

        async loadBarangays(cityCode) {
            this.barangaysLoading = true;
            try {
                const res = await fetch(
                    `https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`,
                );
                const data = await res.json();
                this.barangays = data.sort((a, b) => a.name.localeCompare(b.name));
            } catch (e) {
                console.error('PSGC: Failed to load barangays', e);
            }
            this.barangaysLoading = false;
        },

        onBarangayChange(event) {
            this.selectedBarangay = event.target.value;
        },
    }));
});
