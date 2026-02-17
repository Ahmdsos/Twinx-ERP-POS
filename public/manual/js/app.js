const { createApp, ref, computed, onMounted, watch } = Vue;

createApp({
    setup() {
        const lang = ref(localStorage.getItem('twinx_docs_lang') || 'en');
        const view = ref('hub'); // 'hub' or 'article'
        const activeModuleKey = ref(null);
        const searchQuery = ref('');
        const content = ref(window.docsContent || { en: {}, ar: {} });

        const currentContent = computed(() => content.value[lang.value] || {});

        const currentModule = computed(() => {
            if (!activeModuleKey.value) return null;
            return currentContent.value[activeModuleKey.value];
        });

        // Search Logic
        const filteredContent = computed(() => {
            if (!searchQuery.value) return currentContent.value;
            const q = searchQuery.value.toLowerCase();
            const results = {};

            Object.keys(currentContent.value).forEach(key => {
                const mod = currentContent.value[key];
                // Search in title, description, or any section content
                const inTitle = mod.title.toLowerCase().includes(q);
                const inDesc = mod.description.toLowerCase().includes(q);
                const inBody = mod.sections.some(s => s.title.toLowerCase().includes(q) || s.body.toLowerCase().includes(q));

                if (inTitle || inDesc || inBody) {
                    results[key] = mod;
                }
            });
            return results;
        });

        const setLanguage = (newLang) => {
            lang.value = newLang;
            localStorage.setItem('twinx_docs_lang', newLang);
            document.documentElement.dir = newLang === 'ar' ? 'rtl' : 'ltr';
            document.documentElement.lang = newLang;
        };

        const goHome = () => {
            view.value = 'hub';
            activeModuleKey.value = null;
            searchQuery.value = '';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        const openModule = (key) => {
            activeModuleKey.value = key;
            view.value = 'article';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // Initialize markdown renderer
        const renderMarkdown = (text) => {
            if (!text) return '';
            return marked.parse(text, { breaks: true });
        };

        onMounted(() => {
            setLanguage(lang.value);
        });

        return {
            lang,
            view,
            searchQuery,
            filteredContent,
            currentModule,
            setLanguage,
            openModule,
            goHome,
            renderMarkdown
        };
    }
}).mount('#app');
