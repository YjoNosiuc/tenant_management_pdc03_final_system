document.addEventListener('alpine:init', () => {
    Alpine.data('psgcAddressForm', (config = {}) => ({
        provinces: [],
        cities: [],
        barangays: [],
        selectedProvince: config.initial?.province ?? '',
        selectedProvinceCode: '',
        selectedCity: config.initial?.city ?? '',
        selectedCityCode: '',
        selectedBarangay: config.initial?.barangay ?? '',
        loading: false,
        citiesLoading: false,
        barangaysLoading: false,
        bindParent: config.bindParent ?? false,
        parentKey: config.parentKey ?? null,

        parentModel() {
            if (!this.bindParent || !this.parentKey || !this.$parent) {
                return null;
            }

            return this.$parent[this.parentKey] ?? null;
        },

        async init() {
            await this.loadProvinces();

            if (this.bindParent && this.parentKey) {
                this.$watch(
                    () => this.parentModel()?.id,
                    async (id) => {
                        if (id) {
                            await this.syncFromParentModel(this.parentModel());
                        }
                    },
                );

                const p = this.parentModel();
                if (p?.id) {
                    await this.syncFromParentModel(p);
                }
            } else if (this.selectedProvince) {
                const found = this.provinces.find(
                    (p) => p.name.toLowerCase() === String(this.selectedProvince).toLowerCase(),
                );
                if (found) {
                    this.selectedProvinceCode = found.code;
                    await this.loadCities(found.code);
                }
            }
        },

        async syncFromParentModel(p) {
            if (!p?.id) {
                return;
            }

            this.selectedProvince = p.province || '';
            this.selectedCity = p.city || '';
            this.selectedBarangay = p.barangay || '';

            if (!this.provinces.length) {
                await this.loadProvinces();
            }

            if (this.selectedProvince) {
                const found = this.provinces.find(
                    (pr) => pr.name.toLowerCase() === String(this.selectedProvince).toLowerCase(),
                );
                if (found) {
                    this.selectedProvinceCode = found.code;
                    await this.loadCities(found.code);
                }
            } else {
                this.cities = [];
                this.barangays = [];
                this.selectedProvinceCode = '';
                this.selectedCityCode = '';
            }
        },

        async loadProvinces() {
            this.loading = true;
            try {
                const res = await fetch('https://psgc.gitlab.io/api/provinces/');
                const data = await res.json();
                this.provinces = data.sort((a, b) => a.name.localeCompare(b.name));
            } catch (e) {
                console.error('Failed to load provinces', e);
            }
            this.loading = false;
        },

        async onProvinceChange(event) {
            const selected = this.provinces.find((p) => p.name === event.target.value);
            if (!selected) {
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
            this.pushToParentIfBound();
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
                        (c) => c.name.toLowerCase() === String(this.selectedCity).toLowerCase(),
                    );
                    if (found) {
                        this.selectedCityCode = found.code;
                        await this.loadBarangays(found.code);
                    }
                }
            } catch (e) {
                console.error('Failed to load cities', e);
            }
            this.citiesLoading = false;
        },

        async onCityChange(event) {
            const selected = this.cities.find((c) => c.name === event.target.value);
            if (!selected) {
                return;
            }
            this.selectedCity = selected.name;
            this.selectedCityCode = selected.code;
            this.selectedBarangay = '';
            this.barangays = [];
            await this.loadBarangays(selected.code);
            this.pushToParentIfBound();
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
                console.error('Failed to load barangays', e);
            }
            this.barangaysLoading = false;
        },

        onBarangayChange(event) {
            this.selectedBarangay = event.target.value;
            this.pushToParentIfBound();
        },

        pushToParentIfBound() {
            if (!this.bindParent || !this.parentKey) {
                return;
            }
            const p = this.parentModel();
            if (!p) {
                return;
            }
            p.province = this.selectedProvince;
            p.city = this.selectedCity;
            p.barangay = this.selectedBarangay;
        },
    }));
});
