// Key URLs covered by every check (PL master + one per template + other languages).
module.exports = {
  core: [
    { path: '/', name: 'home' },
    { path: '/produkty/', name: 'shop' },
    { path: '/produkty/dermokosmetyki-weterynaryjne/', name: 'category' },
    { path: '/produkty/dermokosmetyki-weterynaryjne/triaderm-excellence-200ml/', name: 'product' },
    { path: '/potrzeby/', name: 'finder' },
    { path: '/porady/', name: 'guides' },
    { path: '/porady/psy/', name: 'hub-species' },
    { path: '/skladniki/', name: 'ingredients' },
    { path: '/wspolpraca-b2b/', name: 'b2b' },
    { path: '/marka-wlasna/', name: 'private-label' },
    { path: '/znajdz-przedstawiciela/', name: 'reps' },
    { path: '/kontakt/', name: 'contact' },
    { path: '/pobierz/', name: 'downloads' },
  ],
  languages: [
    { path: '/en/', lang: 'en' },
    { path: '/fr/', lang: 'fr' },
    { path: '/ua/', lang: 'uk' },
  ],
};
