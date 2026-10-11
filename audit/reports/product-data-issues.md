# Product data issues found during migration (eurowet.pl, 2026-10)

Every issue below was found while copying product texts verbatim. Nothing was "fixed" silently in the product facts: the new site shows the page text; issues that need a decision are listed for Eurowet.

## amiwet
### Amiwet Szampon dla szczeniąt (`amiwet-szampon-dla-szczeniat`)
- Amiwet page is published directly under /produkty/ (no category page lists it) and has no shop link; badge typo 'sierśc'.
### Amiwet Szampon długa sierść (`amiwet-szampon-dluga-siersc`)
- Amiwet page is published directly under /produkty/ (no category page lists it) and has no shop link; badge typo 'sierśc'.
### Amiwet Szampon krótka sierść (`amiwet-szampon-krotka-siersc`)
- Amiwet page is published directly under /produkty/ (no category page lists it) and has no shop link; badge typo 'sierśc'.
### Amiwet Szampon łagodzący podrażnienia (`amiwet-szampon-lagodzacy-podraznienia`)
- Amiwet page is published directly under /produkty/ (no category page lists it) and has no shop link; badge typo 'sierśc'.
### Amiwet Szampon norkowy (`amiwet-szampon-norkowy`)
- Amiwet page is published directly under /produkty/ (no category page lists it) and has no shop link; badge typo 'sierśc'.
### Amiwet Szampon norkowy małe rasy (`amiwet-szampon-norkowy-male-rasy`)
- Amiwet page is published directly under /produkty/ (no category page lists it) and has no shop link; badge typo 'sierśc'.
### Amiwet Szampon odstraszający pchły i kleszcze (`amiwet-szampon-odstraszajacy-pchly-i-kleszcze`)
- Amiwet page is published directly under /produkty/ (no category page lists it) and has no shop link; badge typo 'sierśc'.

## dermokosmetyki
### Alervet (`alervet`)
- Yoast meta description on all 3 PL pages ("ogranicza skutki świądu. Reguluje złuszczanie naskórka") is stronger than the on-page text ("wspomaga zmniejszanie objawów świądu poprzez korzystny wpływ na regulację procesu złuszczania").
- Alervet 20ml page: title/H1 say "20ml" but OPAKOWANIE says "20x20 ml" (Woo 19193 "Alervet 20ml" at 103.33 PLN is the 20-sachet carton); capacity labelling inconsistent.
- Alervet 500ml subtitle ends with a stray full stop (differs from 200ml/20ml subtitle); heading "Sposób Stosowania" capitalised differently.
- og:image of the PL pages (2019/06/Alervet_200ml-1.png, 2019/06/Alervet_500ml-2.png, 2024/06/Alervet_saszetka_20ml-1.png) and Woo images (2019/06/Alervet_200ml-2.png, 2019/06/Alervet_500ml-1.png) are older packshots than the 2026/06 packshots shown on the pages.
- EN page /en/products/veterinary-dermocosmetics/alervet-20ml-eng/ has a Polish Yoast description; EN page titles carry an "eng"/"ENG" suffix.
- Shop button(s) link without trailing slash (/produkt/alervet-200ml, /produkt/alervet-500ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Alervet excellence (`alervet-excellence`)
- Yoast meta description ("o działaniu przeciwświądowym i łagodzącym stan zapalny skóry") differs from page properties ("łagodzącym świąd i wspomagającym leczenie stanów zapalnych skóry") - stronger claim.
- Badge "PRZECIWŚWIĄDOWY, PRZECIWZAPALNY, NAWILŻAJĄCY" and Woo short description "SZAMPON PRZECIWŚWIĄDOWY Z FITOSFINGOZYNĄ" are stronger than the page subtitle "Szampon pielęgnacyjny, łagodzący świąd z fitosfingozyną".
- Woo product 5206 "Zastosowanie" drops "wspomagająco" ("Pielęgnacja skóry i sierści przy leczeniu stanów zapalnych skóry.").
- Composition typos: "Glicerin", "Polygryceryl-4 Caprate", "Peg 7 Gliceryl Cocoate", "Citric Acids" (Woo version additionally "PAREFUM", "SILICONE QUATERIUM-17").
- Usage text formatting: "spłukać.-częstotliwość: stosować od 2 do 3 razy".
- Related product tile "Hydra-Derm 200ml" actually links to Hydra-Derm Spray (name missing "Spray").
- Page packshot (2026/07/alervet_200ml-2-1.png) differs from Woo images (2018/08/alervet_excellence_200ml-2.png) and from the related-product tiles on other pages (2018/07/alervet_excellence_200ml.png).
- Shop button(s) link without trailing slash (/produkt/alervet-excellence-200ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Alu-derm (`alu-derm`)
- URL slug "alu-derm-200ml" (and EN title "Alu-derm 200ml eng") contradicts title/OPAKOWANIE "210 ml".
- Shop button links to /produkt/alu-derm-210ml but no such WooCommerce product exists (not in the Woo dump) - broken purchase link; no Woo product for Alu-derm.
- Alu-derm is not listed on the category page /produkty/dermokosmetyki-weterynaryjne/.
- Badges use the generic EUROWET logo icon (EUROWET_logotyp_znak.png) instead of product icons.
- Price comparison block still links to SKĄPIEC.pl and NOKAUT.pl (legacy links).
- Composition lists only "CI 77000, ISOBUTYL METHACRYLATE, MEK" although precautions describe a pressurised aerosol ("Zbiornik metalowy pod ciśnieniem") - composition may be incomplete; verify against label.
### Dry&Clean (`dry-clean`)
- Category page /produkty/dermokosmetyki-weterynaryjne/ links the tile to "/dry&clean-150ml" (wrong slug; page is /dryclean-150ml/) and titles it "DRY&Clean 150ml".
- Product name spelled inconsistently: "Dry&Clean", "Dry & Clean", "DRY & CLEAN".
- Badge icons borrowed from Sebovet (SEBOVET_CLEAN_punkt_2.png used twice, SEBOVET_EXCELLENCE_punkt_1.png).
- Usage for a foam says "dodając regularnie szamponu".
- Composition typos: "Glicerin", "Perfum", "Lacid Acids".
- Properties do not state product class (no "dermokosmetyk weterynaryjny" wording) although listed in dermokosmetyki category.
- Shop button(s) link without trailing slash (/produkt/dryclean-150ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Ektovet (`ektovet`)
- PL page Yoast meta description is copied from Hexoderm-K (mentions chlorheksydyna i ketokonazol, which Ektovet does not contain).
- Woo product 24009 Yoast meta description is copied from Alervet ("ALERVET jest dermokosmetykiem...").
- Badges use the generic EUROWET logo icon (EUROWET_logotyp_znak.png).
- Usage text ends without a full stop ("lub według potrzeb").
- Woo-only facts not shown on the product page: "Wolne od: SLS i SLES • parabenów • barwników syntetycznych", storage and safety lines (see notes_verbatim, source /produkt/ektovet-200ml/).
- EN page /en/products/veterinary-dermocosmetics/ektovet-200ml-eng/ has no Yoast meta description.
- Three different uploads of the same packshot (2026/06/Ektovet_200ml-3.png on page, Ektovet_200ml-5.png on category, Ektovet_200ml.png in Woo).
- Shop button uses insecure http:// URL (http://eurowet.pl/produkt/ektovet-200ml/); the Woo product was therefore left unmapped in products.base.json.
### Fyto-derm (`fyto-derm`)
- Name inconsistent: page titles/slugs "Fytoderm 100ml", "Fytoderm 30ml" (slug "fytoderm-30-ml" vs "fytoderm-100ml") vs H1/Woo "Fyto-derm"/"Fyto-Derm".
- Badge says "ZAWIERA GORYCZKĘ ZAPOBIEGAJĄCĄ WYLIZYWANIU RAN" but properties attribute the anti-licking effect to "Charakterystyczny zapach"; composition names no bittering agent - inconsistent claim.
- Woo short description badge "WSPOMAGA REGENERACJĘ I GOJENIE RAN" differs from page badge "WSPOMAGA GOJENIE RAN".
- Both badges use the same icon borrowed from Hexoderm gryzonie (Hexoderm_gryzonie_punkt1.png).
- Properties text "Składniki preparatu wykazują działanie regulujące mikroflorę skóry" names no ingredient; composition (lavender, rosemary, thyme oils, Centella asiatica, horse chestnut) has no stated functions.
- Woo images (2018/07/Fytoderm_100ml-2.png, Fytoderm_30ml-1.png) differ from page packshots (2019/03).
- Shop button(s) link without trailing slash (/produkt/fyto-derm-30ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Hexoderm (`hexoderm`)
- Hexoderm 20ml page: title/H1 "20ml" but OPAKOWANIE "Opakowanie zbiorcze 20 x 20 ml"; Woo product is "Hexoderm 20x20ml" (5213); shop button links to /produkt/hexoderm-20x20ml.
- Packshot filenames misspelled "Hexoder_pies_kot_200ml.png"/"Hexoder_pies_kot_500ml-1.png"; the 200ml page shows the 2018/08 upload while the category page and related tiles use re-uploads 2025/09/Hexoder_pies_kot_200ml.png, 2025/09/Hexoder_pies_kot_200ml-1.png and 2026/06/Hexoder_pies_kot_200ml.png.
- Composition typo "Glicerin".
- Page text names no active ingredient although composition contains Chlorhexidine Digluconate and Cocos Nucifera Oil (no stated functions on the page).
- Shop button(s) link without trailing slash (/produkt/hexoderm-200ml, /produkt/hexoderm-20x20ml, /produkt/hexoderm-500ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Hexoderm gryzonie (`hexoderm-gryzonie`)
- Przeznaczenie says "Dla psów i kotów." (also in Woo 5214 body) while title, subtitle and Woo name say gryzonie / "dla gryzoni i zwierząt futerkowych" - species contradiction; species set from subtitle, needs confirmation.
- Usage, properties and composition are identical to the dog/cat Hexoderm text (no rodent-specific directions).
- Category page lists this product as "Hexoderm 20ml" (without "gryzonie"), blurb "Szampon dermatologiczny dla gryzoni.".
- Title/H1 "20ml" but OPAKOWANIE "Opakowanie zbiorcze 20 x 20 ml"; Woo product "Hexoderm 20x20ml gryzonie".
- Related products on the page are dog/cat products only.
- og:image (2018/07/Hexoderm_saszetka_gryzonie_20ml-1.png) differs from page packshot (2026/06/Hexoderm_gryzonie_20ml.png).
- Shop button(s) link without trailing slash (/produkt/hexoderm-20x20ml-gryzonie) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Hexoderm excellence (`hexoderm-excellence`)
- Badge text typos: "Malasezia" (Malassezia), sentence starting lowercase "działa grzybostatycznie", "Gram dodatnie/Gram ujemne" without hyphens.
- Composition typos: "PAREFUM", "SILICONE QUATERIUM-17", "POLYGRYCERYL-4 CAPRATE", "GLICERIN", "PEG 7 GLICERYL COCOATE", "CITRIC ACIDS", missing space ",TERPINEN-4-OL".
- Terpinen-4-ol is in the composition but not mentioned anywhere in the product text.
- Badge icon for "+CHLORHEKSYDYNA" reuses the "+FITOSFINGOZYNA" icon (HEXODERM_EXCELLENCE_punkt_3.png twice).
- Woo product 5215 text differs from page: properties "o działaniu wspomagającym utrzymanie prawidłowej mikroflory skóry" vs page "ułatwiającym przywrócenie"; Zastosowanie drops "wspomagająco"; short description "SZAMPON REGULUJĄCY MIKROFLORĘ" vs page subtitle "Szampon wspomagający regulację mikroflory".
- Woo product title in capitals "HEXODERM EXCELLENCE 200ML".
- Usage text formatting: "spłukać. -częstotliwość:".
- Page packshot (2026/07/hexoderm_200ml-2.png) differs from Woo images (2018/08/hexoderm_excellence_200ml-2.png) and related-product tiles on other pages (2018/07/hexoderm_excellence_200ml-1.png).
- Shop button(s) link without trailing slash (/produkt/hexoderm-excellence-200ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Hexoderm-K (`hexoderm-k`)
- Hexoderm-K 20ml page: title/H1 "20ml" but OPAKOWANIE "20x20 ml" (Woo 15257 "Hexoderm-K 20ml" at 133.21 PLN is the 20-sachet carton).
- Badges use the generic EUROWET logo icon (EUROWET_logotyp_znak.png).
- Sentence "Jako element pielęgnacyjny stosować przez cały okres właściwego leczenia stanów zapalnych skóry." is duplicated in both Właściwości and Zastosowanie.
- Composition typos: "POLYGRYCERYL-4 CAPRATE", "GLICERIN".
- EN pages (hexoderm-k-200ml-eng, -20ml-eng, -500ml-eng) have Polish Yoast meta descriptions.
- og:image of 20ml page (2023/01/Hexoderm-K_saszetka_20ml.png) and Woo image (2023/01/Hexoderm-K_20ml-scaled.png) differ from page packshot (2026/06/Hexoderm-K_pies_kot_20ml.png).
### Hydra-Derm N (`hydra-derm-n`)
- Related-products block lists the product itself ("Hydra-Derm N 200ml" links to /hydra-derm-n-200ml).
- Name written inconsistently: "HYDRA DERM N" (H1, Woo title) vs "Hydra-Derm N" (title, text).
- Both badges use the same icon (HYDRA_DERM_N_punk_1.png); badge "Z MOCZNIKIEM I KWASEM MLEKOWYM" names ingredients without stated function.
- Composition typos: "GLICERIN", "LACTIC ACIDS".
- Woo image (2019/06/Hydra-derm_N_200ml-2.png) and og:image (2019/06/Hydra-derm_N_200ml-3.png) differ from page packshot (2026/06/Hydra-derm-N_pies_kot_200ml.png).
- Shop button(s) link without trailing slash (/produkt/hydra-derm-n-200ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Hydra-Derm Spray (`hydra-derm-spray`)
- Yoast meta description ("Łagodzi podrażnienia skóry", "likwiduje uczucie świądu") and Woo short description ("ŁAGODZI PODRAŻNIENIA SKÓRY", "LIKWIDUJE ŚWIĄD") are stronger than page text ("Łagodzi objawy związane z podrażnieniami skóry", "łagodzi uczucie świądu").
- Woo product 5217 "Zastosowanie" differs from page ("ze zmianami powstałymi na skutek ogólnych stanów zapalnych, np. alergie, ichtioza" vs page "na zmiany powstałe na skutek nieprawidłowego funkcjonowania skóry, np. towarzyszące alergiom, ichtiozie").
- Composition typos: "Junipures Oxycedrus Wood Oil" (Juniperus), "Silicone Quaterium-17", "Glicerin".
- Related-product tiles across the site call it "Hydra-Derm 200ml" (missing "Spray"); on the Tanaderm page the tile shows the Hydra-Derm N packshot but links to the spray.
- Badges reuse Hydra-Derm N icons (HYDRA_DERM_N_punk_2.png, HYDRA_DERM_N_punk_1.png).
- Properties typography: "Hydra-Derm –spray".
- Shop button(s) link without trailing slash (/produkt/hydra-derm-spray-200ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Peptoderm excellence (`peptoderm-excellence`)
- PL Yoast meta description is the three badge texts concatenated without spaces ("...neutralizacji toksyn.+CHLORHEKSYDYNA Bakteriobójcza...").
- EN page /en/products/veterinary-dermocosmetics/peptoderm-excellence-200ml-eng/ Yoast description is the Polish Alervet excellence text (wrong product, wrong language).
- Badge names "PEPTYD ANTYDROBNOUSTROJOWY AMP" but composition lists only "Monopeptide-17"; the site does not state they are the same - label_name left null, confirm INCI.
- Composition on page "Barbadensis Leaf Juice" is missing "Aloe" (Woo body has "ALOE BARBADENSIS LEAF JUICE"); also "Glicerin", "Peg 7 Gliceryl Cocoate".
- Category/related tile blurb is ungrammatical: "Szampon dermatologiczny regulację mikroflory skóry z Peptydem AMP, Chlorheksydyną, Terpinen-4-ol-em dla psów i kotów."
- Woo product 10333 "Zastosowanie" ends with a double full stop ("Łojotok..").
- Badges use the generic EUROWET logo icon (EUROWET_logotyp_znak.png).
- Page packshot (2026/07/peptoderm_200ml-2.png) differs from Woo images (2021/03/Peptoderm_excellence_200ml-2.png).
### Peroxyvet (`peroxyvet`)
- Duplicate PL pages for the 20ml product: /produkty/dermokosmetyki-weterynaryjne/peroxyvet-20ml/ (ID 19214, linked from category page) and /produkty/dermokosmetyki-weterynaryjne/peroxyvet-20ml-2/ (ID 19228, older packshot 2024/06/Peroxyvet_saszetka_20ml-1.png, legacy SKĄPIEC/NOKAUT links, "Skuteczny" rendered as H1) - one should be redirected.
- Peroxyvet 500ml shop button links to /produkt/peroxyvet-500ml but no such WooCommerce product exists - broken purchase link.
- Yoast meta description typo "pielegnacji" and stronger wording ("Działa regulująco", "Łagodzi podrażnienia skóry oraz ułatwia naturalną odnowę") than page ("Wspomaga procesy regulujące", "Łagodzi objawy związane z podrażnieniami skóry").
- Peroxyvet 20ml page: title/H1 "20ml" but OPAKOWANIE "20x20 ml" (Woo 19197 at 122.69 PLN is the 20-sachet carton).
- Page text names no active ingredient although composition contains Benzoyl Peroxide (no stated function).
- Composition typo "Glicerin".
- EN page /en/products/veterinary-dermocosmetics/peroxyvet-20ml-eng/ has a Polish Yoast description.
- Woo 5670 image (2019/06/Peroxyvet_200ml.png) differs from page packshot (2026/06/Peroxyvet_pies_kot_200ml.png).
- Shop button(s) link without trailing slash (/produkt/peroxyvet-200ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Sebolan (`sebolan`)
- Shop button links to /produkt/sebolan-200ml but no such WooCommerce product exists - broken purchase link; no Woo product for Sebolan.
- Yoast meta description "reguluje mikroflorę skóry" is stronger than page "wspomaga procesy regulujące mikroflorę skóry".
- Same INCI "Junipures Oxycedrus Wood Oil" is called "olejek jałowcowy" here but "dziegieć z jałowca" on Sebovet Clean/Hydra-Derm Spray; INCI misspelled (Juniperus).
- Composition typo "Polisorbate 20".
### Sebostop Spot-on (`sebostop-spot-on`)
- Badge grammar error: "USUWA NADMIAR WOSKOWINY Z PRZEWODZIE SŁUCHOWYM ZEWNĘTRZNYM" (should be "Z PRZEWODU SŁUCHOWEGO ZEWNĘTRZNEGO"); same in Woo short description.
- Badge icons borrowed from Hydra-Derm N (HYDRA_DERM_N_punkt3.png, HYDRA_DERM_N_punk_1.png, HYDRA_DERM_N_punk_2.png).
- Yoast meta description "reguluje mikroflorę naskórka" is stronger than page "wspomaga procesy regulujące mikroflorę naskórka".
- Shop button(s) link without trailing slash (/produkt/sebostop-spot-on-5x2ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Sebovet Clean (`sebovet-clean`)
- Properties section is headed "Działanie" instead of "Właściwości" (inconsistent template; base parser missed it).
- Category page tile blurb is wrong: "Szampon dermatologiczny przeciw świądowi i łupieżowi dla psów." (Sebolan text) instead of "przeciw łojotokowi tłustemu".
- Composition typos: "Glicerin", "Polisorbate 20", "Junipures Oxycedrus Wood Oil" (Juniperus).
- Woo image (2019/06/Sebovet-clean_200ml-1.png) differs from page packshot (2026/06/Sebovet-clean_pies_200ml.png).
- Shop button(s) link without trailing slash (/produkt/sebovet-clean-200ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Sebovet Dry (`sebovet-dry`)
- Properties section is headed "Działanie" instead of "Właściwości".
- Composition typo/missing comma: "Hydrolyzet Keratin Parfum" (Hydrolyzed Keratin, Parfum); "Polisorbate 20".
- Yoast meta description stronger than page: "przywracają prawidłowy stan naskórka", "łagodzą jej podrażnienia" vs page "wspomagają prawidłowy stan naskórka", "łagodzą objawy związane z jej podrażnieniami".
- Properties refer to "Związki czynne zawarte w szamponie" without naming them.
- Category tile uses 2026/06/Sebovet-dry_pies_kot_200ml.png while the page uses 2026/06/Sebovet-dry_pies_kot_200ml-1.png (duplicate uploads); Woo image 2019/06/Sebovet-dry_200ml-2.png is an older packshot.
- Shop button(s) link without trailing slash (/produkt/sebovet-dry-200ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Sebovet excellence (`sebovet-excellence`)
- Badge "WSPOMAGA LECZENIE" is an unspecified therapeutic claim (does not say what it supports).
- Badge text grammar: "Wspomagają procesy przeciwzapalne, antyseptycznie i działają przeciwłupieżowo".
- Chlorhexidine Digluconate is in the composition but not mentioned anywhere in the product text.
- Composition typos: "Cocamidopropyl Betaine Coco Glucoside" (missing comma), "Parefum", "Polisorbate 20", "Silicone Quaterium-17", "Polygryceryl-4 Caprate", "Citric Acids", stray space before comma after Tropaeolum Majus Flower Extract.
- Woo product 5224 "Zastosowanie" ends with stray words "ze skłonnością do łojotoku z oczami."
- Both ingredient badges use the same icon (SEBOVET_EXCELLENCE_punkt_3.png).
- Usage text formatting: "spłukać. -częstotliwość:".
- Page packshot (2026/07/sebovet_200ml-2-1.png) differs from Woo images (2018/08/sebovet_excellence_200ml-2.png) and related tiles on other pages (2018/07/sebovet_excellence_200ml.png).
- Shop button(s) link without trailing slash (/produkt/sebovet-excellence-200ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Tanaderm (`tanaderm`)
- Yoast SEO title is a question-style headline ("Spray Tanaderm na Opuszki Palców - Jak Złagodzić Objawy Podrażnienia? - EUROWET") inconsistent with other product pages.
- Yoast meta description stronger than page: "o działaniu kojącym na uszkodzone opuszki palców oraz przyspieszającym odnowę naskórka", "działanie regulujące mikroflorę skóry" vs page "łagodzącym objawy związane z uszkodzeniami opuszek palców oraz wspomagającym procesy odnowy naskórka", "wpływają na utrzymanie prawidłowej mikroflory skóry".
- Usage typo "wzmocniena" (wzmocnienia).
- Woo short description badge "REGENERUJĄCY" vs page badge "WSPOMAGAJĄCO – REGENERUJĄCY".
- Badge icons borrowed from a Hexoderm horse product (Hexoderm_konie_punkt1.png, Hexoderm_konie_punkt2.png).
- Related tile "Hydra-Derm 200ml" shows the Hydra-Derm N packshot but links to /hydra-derm-spray-200ml.
- Page names no active ingredient; composition (Trichloroacetic acid, Centella Asiatica Extract, Aloe Barbadensis Leaf Juice) has no stated functions.
- Shop button(s) link without trailing slash (/produkt/tanaderm-90ml) - relies on redirect; the Woo product(s) were therefore left unmapped in products.base.json.
### Triaderm excellence (`triaderm-excellence`)
- PL page Yoast meta description is copied from Hexoderm excellence ("Hexoderm excellence jest dermokosmetykiem...").
- Woo product 24870 Yoast meta description is copied from Alervet ("ALERVET jest dermokosmetykiem...").
- Properties paragraph does not name the product ("Szampon hipoalergiczny z konwalnikiem japońskim i fitosfingozyną jest dermokosmetykiem...").
- Page subtitle omits target species ("SZAMPON HIPOALERGICZNY Z KONWALNIKIEM JAPOŃSKIM I FITOSFINGOZYNĄ"); category tile calls it "Szampon dermatologiczny z fitosfingozyną i konwalnikiem japońskim".
- Indications typo: space before comma ("infekcjach skórnych , alergii").
- Ingredient descriptions (Opis składników), "Wolne od", PAO and EAN appear only on the Woo page /produkt/triaderm-excellence-200ml/, not on the /produkty/ page (stated_function_verbatim and notes_verbatim taken from Woo).
- Badges use the generic EUROWET logo icon (EUROWET_logotyp_znak.png).
- EN page /en/products/veterinary-dermocosmetics/triaderm-excellence-200ml-eng/ has no Yoast meta description.

## karmy
### EFADERM-E (`efaderm-e-plyn-pies`)
- Shop button links to /produkt/efaderm-e-30ml-pies, which is not a published WooCommerce product (no Woo product for Efaderm-E 30 ml in the REST dump) - the liquid cannot be bought on eurowet.pl.
- Grammar error in 'Zastosowanie': "ułatwia utrzymanie w dobrej kondycji skóry oraz sierść" (should be 'sierści'); same sentence on the cat 30 ml page.
- H2 lead ('Działanie') and H3 'Zastosowanie' texts are swapped compared with the cat 30 ml page (kot page has the lead under H2 'Zastosowanie').
- Related-products box labels the cat and dog liquids 'Efaderm-E 30ml' / 'Efaderm-e 30ml' (inconsistent capitalisation, no species in label).
- EN page /en/products/complementary-foods/efaderm-e-30ml-pies-eng/ last modified 2019-03-18 vs PL 2026-06-10 - translation likely outdated.
### EFADERM-E (`efaderm-e-plyn-kot`)
- Shop button links to /produkt/efaderm-e-30ml-kot, which is not a published WooCommerce product (no Woo product for Efaderm-E 30 ml kot in the REST dump).
- Page has two sections both titled 'Zastosowanie' (H2 lead and H3); the H2 lead holds the regeneration text while the dog 30 ml page uses 'Działanie' with the swapped texts.
- Usage text missing a full stop: "Podawać przez okres do 2 miesięcy Podawać bezpośrednio...".
- Grammar error: "ułatwia utrzymanie w dobrej kondycji skóry oraz sierść" (should be 'sierści').
- Composition lists the antioxidant as 'E306' (old numbering) while the dog 30 ml page uses '1b306(i)'.
- EN page title is just 'EFADERM-E' (no capacity/species), unlike other EN product titles.
### EFADERM-E (`efaderm-e-kapsulki-pies`)
- Shop button links to /produkt/efaderm-e-60kaps-psy, which is not a Woo product; the Woo product is ID 5245 'EFADERM-E 60 kaps. psy' at /produkt/efaderm-e-60szt-psy/.
- Woo product 5245 description contains a typo: "nadmiernego wypadania sierścii".
- EN page /en/products/complementary-foods/efaderm-e-60pcs-psy-eng/ shows the 30 ml liquid packshot (Efaderm-E_plyn_pies_30ml) and describes the 30 ml liquid (ml dosing, borage/maize-germ oil composition), not the 60-capsule product.
### EFADERM-E (`efaderm-e-kapsulki-kot`)
- Page not listed on the category page /produkty/karmy-uzupelniajace/ and last modified 2022-10-24; no Woo product exists - likely discontinued but still published and indexable.
- Shop button links to /produkt/efaderm-e-60kaps, which is not a Woo product.
- Composition typo: "Składniki analityczne: aminokwasyoleje i tłuszcze surowe 70,9%" and stray space before the final full stop ("666 mg .").
- No OPAKOWANIE section (capacity only in H2 '60kaps.').
- Subtitle 'Dietetyczna karma uzupełniająca dla kotów' differs from the 'Karma uzupełniająca' wording on the other Efaderm-E pages.
- Related-products box links legacy root URLs /fytoderm-100ml, /zn-a-b6-60tab, /sebovet-excellence-200ml.
- EN page /en/products/complementary-foods/efaderm-e-60pcs-koty-eng/ shows the 30 ml liquid packshot and describes the 30 ml liquid (30 ml, ml dosing), not the 60 capsules.
### Elektrolity (`elektrolity`)
- Page is not linked from the category page /produkty/karmy-uzupelniajace/ although Woo product 15222 is in stock (reachable only via related boxes on Smoczki pages).
- Yoast meta description is the full 'Właściwości' text (~600 characters) - far too long for a meta description.
- Analytical constituents given in grams without a reference quantity ("Białko surowe 22,3 g, włókno surowe: 0,2 g...") - unclear whether per 100 g or per sachet.
- Related-products box links legacy root URLs /milvet-300g and /karbowital-125ml.
### MILVET (`milvet`)
- Milvet 800g page: OPAKOWANIE says "300g ilość netto" (copied from the 300 g page).
- Composition on all 3 pages (and Woo) reads "witamina B12 (cynkobalamina)" - typo for cyjanokobalamina.
- Product is a 'Pełnoporcjowy' (complete) milk replacer but is listed in the category 'Karmy uzupełniające'.
- Badge 'BUTELKA+SMOCZEK WEWNĄTRZ OPAKOWANIA' appears only on the 300 g and 800 g pages, not on 10 x 100 g.
- Milvet 300g page is linked from the category page and related boxes only via the legacy short URL /milvet-300g.
- Milvet 10 x 100g related box links old slug /produkty/karmy-uzupelniajace/wita-vet-junioradult-32g-30szt/ (current page: /produkty/karmy-uzupelniajace/wita-vet-complex-32g-30szt/).
- The Milvet Yoast description is wrongly reused as meta description on both Smoczki pages.
### NUTRIKARD (`nutrikard`)
- Shop button links to /produkt/nutrikard-40tab/, which is not a published Woo product (no Woo product for Nutrikard).
- Page not listed on the category page /produkty/karmy-uzupelniajace/; last modified 2023-12-06.
- Badge typos: "rdestowiEc ostrokończysty" and "układu Sercowo - naczyniowego".
- 'Zastosowanie' text lacks full stops: "...układu sercowo - naczyniowego Dostarcza energię... Działa ochronnie na układ krążenia".
- Composition spells the olive species 'Olea europea' (correct: Olea europaea).
- Subtitle uses 'Mieszanka paszowa uzupełniająca' while the rest of the category says 'Karma uzupełniająca'.
- Text makes cardiovascular/medical-sounding claims for a feed ("profilaktyce powstawania zatorów", "wpływa korzystnie na ciśnienie krwi") - needs regulatory review before reuse.
- Related box links old slug /produkty/karmy-uzupelniajace/wita-vet-junioradult-8g-80szt/ and legacy /hexoderm-excellence-200ml.
### Olej z łososia (`olej-z-lososia`)
- Page titles 'Olej  łososia 200 ml' / 'Olej  łososia 500 ml' are missing 'z' (double space); the 500 ml slug /olej-lososia-500-ml/ also lacks 'z' and the 200 ml slug /olej-z-lososia/ has no capacity.
- Yoast meta descriptions (both pages) use older wording with stronger claims than the page: "Kwas EPA działa przeciwzapalnie", "obniża poziom cholesterolu w krwi" (page: "Kwas EPA wspiera naturalne procesy...").
- No OPAKOWANIE section on either page (capacity only in title/H2).
- Page text claims "znacząco obniża poziom cholesterolu we krwi" - health claim for a feed; needs regulatory review before reuse.
### WITA-TONYL (`wita-tonyl-gryzonie`)
- 'Przeznaczenie' and subtitle list myszy, but 'stosowanie' gives no dose for mice (only króliki, kawie i chomiki, żółwie).
- Grammar in dosing: "kawie i chomiki 2 kropli / dzień" (should be '2 krople').
- Composition additive code malformed: "E3a831 witamina B6" (should be 3a831).
- Linked from the category page and related boxes only via legacy short URL /wita-tonyl-gryzonie-30ml.
- Yoast description uses older wording ("w szczególności czynników krwiotwórczych") than the on-page text.
### WITA-TONYL (`wita-tonyl-psy-koty`)
- Shop button links to /produkt/wita-tonyl-30-ml-psy-koty, which is not a published Woo product (no Woo product for the dog/cat version).
- Page not listed on the category page /produkty/karmy-uzupelniajace/; last modified 2023-12-06.
- 'Właściwości' text on the dog/cat product says "potrzeb żywieniowych małych zwierząt" (copied from the rodent version).
- Double full stop "funkcjonowanie organizmu.." (page and Yoast).
- Composition: "3a314 niacyna 30 000" has no unit; malformed code "E3a831 witamina B6".
- Related box links old slugs /produkty/karmy-uzupelniajace/wita-vet-junioradult-1g-400szt/ and /wita-vet-junioradult-32g-30szt/ plus legacy /wita-tonyl-gryzonie-30ml.
### WITA-VET Complex 1g (`wita-vet-complex-1g`)
- Shop buttons link to /produkt/wita-vet-junior-adult-1g-80tab and /produkt/wita-vet-junior-adult-1g-400tab, which are not Woo paths (Woo 5251 /produkt/wita-vet-junior-adult-1g-80szt/, 5252 /produkt/wita-vet-junior-adult-1g-400szt/).
- Woo slugs and EN page titles still use the old name 'WITA-VET junior+adult 1g' while PL pages and Woo titles say 'Complex'.
- Badge formatting differs from the 3,2 g / 8 g Complex pages (mixed-case 'Junior+ Adult, Ca/P 1,3' vs upper-case 'JUNIOR + ADULT | CA/P 2').
- 400 tab page related box links old slug /produkty/karmy-uzupelniajace/wita-vet-junioradult-1g-80szt/ and legacy /wita-tonyl-* URLs.
- Per-kg additive values differ by orders of magnitude between tablet sizes (e.g. witamina B12: 40,15 mg in 1 g, 670 mg in 3,2 g, 5000 mg in 8 g; witamina D3: 0,125 mg / 318,8 mg / 12,5 mg) - verify against the labels.
### WITA-VET Complex 3,2g (`wita-vet-complex-3-2g`)
- 3,2g 100 tab page: OPAKOWANIE says "Opakowanie zawiera: 30 tab. Masa netto: 96g." (copied from the 30 tab page).
- Composition lists "3c361 DL-metionina" - 3c361 is the code used for L-arginina on the other Wita-Vet pages (DL-metionina is 3c301 elsewhere).
- Shop buttons link to /produkt/wita-vet-junior-adult-32g-30tab and -32g-100tab, which are not Woo paths (Woo 5254 /produkt/wita-vet-complex-3g-30-tab-witaminy-dla-psow/, 5255 /produkt/wita-vet-junior-adult-32g-100szt-witaminy-dla-psow/).
- Woo slug for 30 tab says '3g' (/produkt/wita-vet-complex-3g-30-tab-...) instead of 3,2 g.
- Several other pages link this product via old slugs /produkty/karmy-uzupelniajace/wita-vet-junioradult-32g-30szt/ and -32g-100szt/.
- Per-kg additive values differ by orders of magnitude between tablet sizes (e.g. witamina B12: 40,15 mg in 1 g, 670 mg in 3,2 g, 5000 mg in 8 g; witamina D3: 0,125 mg / 318,8 mg / 12,5 mg) - verify against the labels.
### WITA-VET Complex 8g (`wita-vet-complex-8g`)
- Dosing line for adults is missing 'do': "Psy dorosłe: 1 tab./ 40kg m.c./1 raz dziennie" (puppies: 'do 20kg').
- Page says 'dla psów wszystkich ras' but related-product cards elsewhere describe it as 'Karma uzupełniająca dla psów dużych ras'.
- Shop buttons link to /produkt/wita-vet-junior-adult-8g-30tab and -8g-80tab, which are not Woo paths (Woo 5256 /produkt/wita-vet-complex-8g-30-tab-witaminy-dla-psow/, 5257 /produkt/wita-vet-complex-8g-80-tab-witaminy-dla-psow/).
- Other pages link this product via old slugs /produkty/karmy-uzupelniajace/wita-vet-junioradult-8g-30szt/ and -8g-80szt/.
- Per-kg additive values differ by orders of magnitude between tablet sizes (e.g. witamina B12: 40,15 mg in 1 g, 670 mg in 3,2 g, 5000 mg in 8 g; witamina D3: 0,125 mg / 318,8 mg / 12,5 mg) - verify against the labels.
### WITA-VET ENERGIA (`wita-vet-energia-kot`)
- 60 tab page composition has malformed additive codes ("3.2.3 L-lizyna HCl", "3c3.6.1 L-arginina", "E4 siarczan miedzi") while the 120 tab page uses 3c322 / 3c361 / 3b405.
- 120 tab page: "Masa netto całościowa: 23.4 g" (decimal point instead of comma).
- 'Właściwości' text missing a full stop: "mineralizację kości Produkt wyjątkowo smaczny" (all versions).
- Shop buttons link to /produkt/wita-vet-energia-kot-60-tab-5-blistrow/ and /produkt/wita-vet-energia-kot-120-tab-10-blistrow/, which differ from the Woo paths (16715 .../wita-vet-energia-kot-60-tab-5-blistrow-witaminy-dla-kotow/, 16718 .../wita-vet-energia-kot-120-tab-10-blistrow-witaminy-dla-kotow/).
- Two legacy pages still published but not listed on the category page: 'WITA-VET KOT junior+adult energia 0,195g 30szt.' (2022) and 'WITA-VET junior+adult kot ENERGIA 24szt.' (2020); both shop buttons point to /produkt/wita-vet-junior-adult-energia-30tab/, which is not a Woo product.
- Legacy 24szt page: title says 24szt but H2 and OPAKOWANIE say 30; it calls the product 'Mieszanka paszowa uzupełniająca' and gives different dosing ("Kotki ciężarne: 1 szt. rano i wieczorem (z wyjątkiem ostatniego tyg. ciąży)", "Kotki karmiące w okresie laktacji: 2 szt. rano i wieczorem", adults "przez 7-14 dni w miesiącu") - conflicts with the current daily dosing.
- Legacy 0,195g 30szt page Yoast description is for a dog product ("WITA-VET Ca/P=1,3 jest karmą uzupełniającą, przeznaczoną dla psów...").
### WITA-VET SIERŚĆ (`wita-vet-siersc-kot`)
- Current composition (60/120 tab) differs from the legacy 30szt/24szt pages (e.g. 'witamina E 30 g' vs '15000 IU', 'DL-metionina 50 mg' vs '50000 mg', 'jodek potasu 277 mg' vs 'jodan wapnia 308 mg') - verify which values are on the current label.
- Composition missing a full stop: "spoiwo - krzemionka Składniki analityczne"; 'Właściwości' text ends without a full stop.
- 60 tab page shop button links to /produkt/wita-vet-siersc-kot-60-tab-5-blistrow/, which differs from the Woo path (16722 /produkt/wita-vet-siersc-kot-60-tab-5-blistrow-witaminy-na-siersc-dla-kotow/).
- Legacy page 'WITA-VET kot SIERŚĆ 30szt.' (2022): title and H2 say 30 tab. but OPAKOWANIE says 24 tab.
- Legacy page 'WITA-VET junior+adult kot SIERŚĆ 24szt.' (2020): title says 24szt but H2/OPAKOWANIE say 30 szt.; badge typo 'ZDROWA SKRÓRA'; 'Mieszanka paszowa uzupełniająca'; dosing 'przez 8-10 dni w miesiącu' conflicts with current daily dosing.
- Both legacy pages are not listed on the category page and their shop buttons point to /produkt/wita-vet-siersc-30tab/, which is not a Woo product.
### WITA-VET sierść 2g (`wita-vet-siersc-pies`)
- Shop buttons link to /produkt/wita-vet-siersc-2g-30tab/ and /produkt/wita-vet-siersc-2g-100tab/, which are not Woo paths (Woo 8059 /produkt/wita-vet-siersc-2g-30szt-witaminy-dla-psow/, 8060 /produkt/wita-vet-siersc-2g-100szt-witaminy-dla-psow/).
- Woo 8059 and 8060 Yoast descriptions start with a typo: "KDZIENNA DAWKA WITAMIN I MINERAŁÓW".
- Composition punctuation: "3a880 D-biotyna 218 mg, Aminokwasy:" (comma instead of full stop).
- Related boxes link old slugs /produkty/karmy-uzupelniajace/wita-vet-junioradult-32g-100szt/, -8g-30szt/, -8g-80szt/.
- EN slug inconsistency: /wita-vet-fur-2g-30pcs/ (no '-eng') vs /wita-vet-fur-2g-100pcs-eng/.
### WITA-VET sierść (`wita-vet-siersc-krople`)
- Woo product 5259 'WITA-VET SIERŚĆ 25ml' is out of stock ('Brak w magazynie').
- Shop button links to /produkt/wita-vet-siersc-25ml, which is not the Woo path (5259 /produkt/wita-vet-siersc-25ml-witaminy-dla-psow-i-kotow/).
- 'Właściwości' text has sentences starting lowercase ("sprawdzony skład...", "odpowiedni poziom miedzi...") and a mid-sentence capital ("do Prawidłowego formowania"); the same errors are copied into the Yoast description.
- Composition: additive code '3a481 D-pantotenian wapnia' (elsewhere 3a841) and unit 'UI' instead of 'IU'.
- Text claims vitamin E 'wzmacnia również naczynia krwionośne' and copper prevents 'przedwczesnego siwienia' - claims to review before reuse.
### WITA-VET junior + adult (`wita-vet-junior-adult-krople`)
- Composition punctuation/typos: "chlorek sodu, Dodatki w 1 kg", missing full stops before 'Pierwiastki śladowe' and 'Składniki analityczne', "włókno surowe 0, 0 %".
- Related box links old slug /produkty/karmy-uzupelniajace/wita-vet-junioradult-1g-80szt/ and legacy /wita-tonyl-gryzonie-30ml.
- Category-page card label is 'Wita-Vet junior+adult 25ml' while the page H1 is 'WITA-VET junior + adult' and Woo is 'WITA-VET JUNIOR + ADULT 25ml' (naming inconsistent with the 'Complex' rename of the tablets).
### WITA-VET senior (`wita-vet-senior`)
- Shop button links to /produkt/wita-vet-senior-80tab, which is not the Woo path (5258 /produkt/wita-vet-senior-80-tab-witaminy-dla-psow/).
- 'Zastosowanie' has a stray space: "Dzienna dawka witamin i minerałów . Odporność."
- Yoast description lacks spaces after full stops ("w dobrym stanie.Koenzym Q10...").
- Composition labels coenzyme Q10 as "koenzym (Q10)".
### WITA-VET SPORT (`wita-vet-sport`)
- Shop button links to /produkt/wita-vet-sport-12g-30tab-dla-psow/, which is not a published Woo product (no Woo product for Wita-Vet Sport).
- Page not listed on the category page /produkty/karmy-uzupelniajace/; last modified 2023-12-06.
- OPAKOWANIE says 'Masa netto ilościowa: 36 g' (elsewhere 'całościowa').
- Yoast description differs from page and is promotional ("dla 1 do 20 kg psów, 1 tab./raz dziennie. Witaminy dla psa!").
- Composition spacing typo "L-winian-L- karnityny".
- Related box links legacy /wita-tonyl-* URLs and old slug /produkty/karmy-uzupelniajace/wita-vet-junioradult-1g-80szt/.
### WITA-VET STAWY (`wita-vet-stawy-kot`)
- Shop button links to /produkt/wita-vet-stawy-0195g-30-tab-dla-kotow/, which is not a published Woo product (no Woo product for the cat version).
- Page not listed on the category page /produkty/karmy-uzupelniajace/; last modified 2023-12-05.
- Packshot file name 'Wita_vet_Pies_STAWY_560mg_30kaps_op-1.png' refers to a dog / 560 mg capsule product while the page is for cat 0,195 g tablets - verify the image.
- Unlike the dog page, no per-tablet active-ingredient amounts are given.
- Text names conditions ("dysplazja, zmiany zwyrodnieniowe, uszkodzenia po przebytym urazie") for a feed - needs vet-context framing/regulatory review.
- Related box links legacy /kolorpielegnacja-szampon-dla-kotow-200ml.
### WITA-VET stawy (`wita-vet-stawy-pies`)
- Shop button links to /produkt/wita-vet-stawy-4-g-30-tab-dla-psow/, which is not the Woo path (13924 /produkt/wita-vet-stawy-4g-30-tab-dla-psow-chondroityna-glukozamina-czarci-pazur-kolagen-rybi-msm-witamina-c/ - keyword-stuffed slug).
- Target group inconsistent: 'Właściwości' says "Można podawać młodym psom w okresie intensywnego wzrostu" while 'Zastosowanie' says "zalecana jest dla psów dorosłych z problemami kostno-stawowymi".
- Dosing given only as "1 tabletka dziennie na 40kg masy" - no guidance for dogs under 40 kg.
- Typos in active-ingredient list: "ekstrakt z dzikiej roży", "(witamina c)", "msm (Methyl - Sulfonyl - Methan)".
### WITAZIUM (`witazium`)
- Shop button links to /produkt/wyzium-40szt/ (typo) - the Woo product is 5264 at /produkt/witazium-40szt/.
- Typo "oligo-elemeny" (should be 'oligoelementy') on page and in Yoast description.
- Two sections both titled 'Skład' (per kg and per tablet).
- Claims "Opóźnia zmętnienie soczewki" and use in animals with "niewydolnością serca lub nerek" - needs vet-context framing/regulatory review before reuse.
### Smoczki do karmienia kotów (`smoczki-do-karmienia-kotow`)
- Yoast meta description is the Milvet milk-replacer text (wrong product).
- No subtitle on page; H1 'SMOCZKI DO KARMIENIA kotów' has inconsistent capitalisation.
- Accessory is published under /produkty/karmy-uzupelniajace/ while its Woo category is 'akcesoria-dla-zwierzat'; page is not listed on the category page.
- Stray lowercase fragment "pasuje do butelki" inside the description.
- Related box links legacy /milvet-300g.
- EN page is an untranslated copy title '2 X SMOCZKI DO KARMIENIA KOTÓW ENG'.
### Smoczki do karmienia psów (`smoczki-do-karmienia-psow`)
- Yoast meta description is the Milvet milk-replacer text (wrong product).
- 'pasuje do butelki' is marked up as an H1 heading.
- Accessory is published under /produkty/karmy-uzupelniajace/ while its Woo category is 'akcesoria-dla-zwierzat'; page is not listed on the category page.
- Related box links legacy /milvet-300g.
- EN page title is untranslated ('2 X SMOCZKI DO KARMIENIA PSÓW ENG').
### Butelka do karmienia zwierząt (`butelka-do-karmienia-zwierzat`)
- Accessory is published under /produkty/karmy-uzupelniajace/ while its Woo category is 'akcesoria-dla-zwierzat'; page is not listed on the category page.
- Related box describes Efaderm-e 30ml as 'Mieszanka paszowa uzupełniająca dla psów' (page itself says 'Karma uzupełniająca') and links legacy /efaderm-e-30ml-pies and /milvet-300g.

## kolorpielegnacja
### Drop&Go (`kp-drop-and-go-krople`)
- Product page header shows capacity label "200ml" although the product is 5x2 ml (Opakowanie: "5x2 ml").
- Sposób stosowania on the product page is truncated: "- psy i koty o m.c.  45 kg - 3 pipety" (weight bands lost). Full dosing exists only on the Woo product /produkt/dropgo-krople-przeciwko-pchlom-kleszczom-i-innym-owadom/: "– psy i koty o m.c. < 20 kg – 1 pipeta – psy o m.c. 20-45 kg – 2 pipety – psy o m.c. > 45 kg – 3 pipety".
- Page title grammar: "krople ochronne przeciw pchłom i kleszczom i innym owadom" (double "i"); Woo name uses "krople przeciw pchłom, kleszczom i innym owadom", Woo slug uses "przeciwko".
- Absolute/unsubstantiated safety claims in Właściwości: "Bezpieczne dla ciebie, twoich bliskich i środowiska! Bez alergii i podrażnień." (compliance risk for a cosmetic).
- Related-product card for Pianka ochronna on this page reuses the spray description: "Spray ochronny przeciw pchłom i kleszczom dla psów i kotów. Naturalnie bezpieczny."
- Neem paragraph misspells the insect species "Anticaria gemmatalis" (Anticarsia gemmatalis).
- Contact block phone on this page (+48 62 753 04 47) differs from the other product pages (+48 62 580 45 15).
- EN page Yoast description is in Polish.
- Środki ostrożności omit the age/pregnancy restrictions stated for the other Neem products of the line (spray/szampon ochronny: "Nie przeznaczony dla psów i kotów poniżej 3 miesiąca życia oraz dla suk i kotek ciężarnych lub karmiących"; pianka: "Nie przeznaczona dla psów i kotów poniżej 1 roku życia") — inconsistent across the Neem range; confirm with producer.
### Dezodorant - Neutralizator zapachów (`kp-dezodorant-neutralizator-zapachow`)
- Zastosowanie (indications) field holds the product form ("Dezodorant w sprayu") instead of an indication.
- Kompleks prebiotyczny named in badges is not identifiable by name in the INCI list.
- 200ml page "PRZEJDŹ DO SKLEPU" links to /produkt/dezodorant-neutralizator-zapachow-200ml/, which is not the Woo slug (/produkt/dezodorant-neutralizator-zapachow-dla-psow-i-kotow/) and is absent from the product sitemap (404 or redirect).
- EN 200ml page Yoast description is the Polish eye-cleanser text ("Płyn łagodzi podrażnienia spojówek oraz ułatwia naturalne nawilżenie oczu i powiek...") — wrong product; EN 100ml Yoast description is in Polish.
- Woo descriptions end "Stosować w zależności od potrzeb.." (double period); Woo 200ml slug carries no capacity.
- Related products on the page (szampon York, szampon na sucho, szampon dla szczeniąt) all link to 200ml/150ml legacy short URLs (/kolorpielegnacja-...) rather than canonical /produkty/kolekcja-kolorpielegnacja/... paths.
### Emulsja do przemywania uszu (`kp-emulsja-do-przemywania-uszu`)
- Właściwości mixes in an indications sentence ("Wskazania: higiena zewnętrznego przewodu słuchowego i małżowiny usznej.") while the Zastosowanie field says only "Higiena uszu.".
- Woo product name is "EMULSJA DO USZU" vs page "EMULSJA DO PRZEMYWANIA USZU".
- INCI typo: "Glicerin" (Glycerin).
- No stated function for the highlighted ingredient (olejek migdałowy) on the page.
### Gold Regenerator sierści i skóry (`kp-gold-regenerator-siersci-i-skory`)
- Inconsistent product type on the same page: subtitle "Mieszanka paszowa uzupełniająca" vs Właściwości "Karma uzupełniająca".
- Page H1 is the generic word "Skuteczny"; product name appears only as an H2 ("GOLD REGENERATOR SIERŚCI I SKÓRY"); capacity "25ml" is rendered as a heading.
- No "Środki ostrożności" section on the product page.
- Product page omits feed-label statements present on the Woo product: "Zawiera białko pochodzenia zwierzęcego, do karmienia zwierząt innych niż przeżuwacze." and "W razie potrzeby ilość podawanych kropli można zwiększyć."
- Hydrolizat miazgi wątrobowej (named in Właściwości) is not identifiable in Skład, which lists only "produkty pochodzenia zwierzęcego".
- Broken sentence in Właściwości: "Ze względu na zawartość aminokwasów, takich jak: ... walina. Wpływa korzystnie na prawidłowe funkcjonowanie organizmu Hydrolizat wspomaga pracę  wątroby." (fragment, missing period, double space).
- Woo product text differs from page ("czynników krwiotwórczych", "Hydrolizat wspomaga czynności komórek wątroby" in Woo Yoast description); Woo og_description is the generic site slogan.
- PL Yoast description is badges + Właściwości starting mid-paragraph ("Pomaga także...") and is far too long (~600 chars).
- Related card "Szampon biała sierść 200ml" links to /szampon-biala-siersc-200ml (no such page; real page /produkty/kolekcja-kolorpielegnacja/kolorpielegnacja-szampon-biala-siersc-200ml/) and shows the legacy Amiwet packshot amiwet_szampon_biala_siersc_200ml.png.
- Nokaut.pl price-comparison link points to a different product ("wita-vet-kot-siersc-regenerator-siersci-i-skory-24-tabletki").
- Product is not listed on the Kolekcja Kolor & Pielęgnacja category page (orphan); page last modified 2023-12-07, older layout (H1 "Skuteczny").
- A complementary feed (vitamin drops) sits in a cosmetics collection; product_type should be reviewed for navigation (it also fits karmy-uzupelniajace).
### Odżywka bez spłukiwania (`kp-odzywka-bez-splukiwania`)
- Woo short descriptions of both variants (8487, 10832) are copied from Szampon łagodzący: "ŁAGODZI OBJAWY ZWIĄZANE Z PODRAŻNIENIAMI SKÓRY / OGRANICZA ZŁUSZCZANIE NASKÓRKA / + OLEJEK JAŁOWCOWY" — wrong product claims in the shop.
- Typo in Zastosowanie: "Odżywka do siersci i włosa." (siersci → sierści); Woo version reads "Pielęgnacja sierści i włosa."
- Category page card says "Odżywka dla psów." while the product page says "dla psów i kotów".
- "Kompleks nawilżający" named in badges is not identifiable by name in the INCI list; no ingredient-level functions stated.
- EN 200ml and 1000ml pages carry the Polish eye-cleanser Yoast description ("Płyn łagodzi podrażnienia spojówek...") — wrong product.
### Odżywka do sierści i włosów (`kp-odzywka-do-siersci-i-wlosow`)
- INCI typos: "Silicone Quaterium-17" (Quaternium), "Citric Acids"; Woo INCI uses "PERFUM" (Parfum).
- EN page has an empty Yoast description.
- No stated function for the highlighted ingredient (olejek z migdałowca).
### Pianka ochronna przeciw pchłom i kleszczom (`kp-pianka-ochronna-przeciw-pchlom-i-kleszczom`)
- Woo Yoast description names the wrong product: "SPRAY OCHRONNY PRZECIW PCHŁOM I KLESZCZOM działa ochronnie..." and states a stronger claim ("działa ochronnie") than the page ("wspomaga działanie ochronne").
- Related/category cards (Drop&Go page, /produkty/kolekcja-kolorpielegnacja/, /produkty/preparaty-odstraszajace-pchly-i-kleszcze/) describe the foam as "Spray ochronny przeciw pchłom i kleszczom dla psów i kotów."
- Badge casing "BEZPIECZNa, skuteczna, naturalna"; Sposób stosowania starts lowercase ("wyczesać"); INCI typos "Glicerin", truncated "Methylisothiazolinon".
- Ceneo link searches "eurowet spray ochronny" instead of the foam.
- Neem paragraph misspells "Anticaria gemmatalis" (Anticarsia gemmatalis).
- EN page Yoast description is in Polish.
### Płyn do przemywania oczu (`kp-plyn-do-przemywania-oczu`)
- INCI typo: "Ortofosforic Acid" (Orthophosphoric Acid).
- Woo product name "PŁYN DO OCZU" vs page "PŁYN DO PRZEMYWANIA OCZU".
- Related-card packshot file is named "EUROWET_emulsja_do_przemywania_oczu_kolor_pielegnacja_100ml.png" (emulsja vs płyn) — media naming inconsistency.
- No stated function for the highlighted ingredient (woda chabrowa).
### Spray ochronny na opuszki palców (`kp-spray-ochronny-na-opuszki-palcow`)
- Key ingredients (aloes, wąkrota azjatycka) are named only in the Woo short description ("+ ALOES, WĄKROTA AZJATYCKA"), not on the product page; page states only a collective function ("Jego składniki wspomagają utrzymanie prawidłowej mikroflory skóry oraz działają wysuszająco, ściągająco i kojąco.").
- Woo short description claims ("IDEALNY NA: ŚNIEG, SÓL ROZGRZANA POWIERZCHNIA TWARDE PODŁOŻE") differ from page badges ("regenerujący", "BEZ ŚLADÓW"); badge casing inconsistent ("regenerujący").
- Spacing typo "pielęgnująco -ochronnym na  opuszki"; INCI typo "Glicerin".
- Described as "Dermokosmetyk weterynaryjny" while placed in the Kolor & Pielęgnacja cosmetics collection.
### Spray ochronny przeciw pchłom i kleszczom (`kp-spray-ochronny-przeciw-pchlom-i-kleszczom`)
- Leaflet PDF is linked only from the Woo products, with a double slash: "https://eurowet.pl//wp-content/uploads/katalog/EUROWET_produkty_odstraszajace_owady.pdf"; product pages have no documents.
- Woo descriptions add claims absent from the product page ("Bezzapachowy", "biodegradowalny", "Skuteczny przeciw pchłom, kleszczom i innym insektom"); Woo Yoast says "działa ochronnie" vs page "wspomaga działanie ochronne".
- PL Yoast description says "NATURALNIE BEZPIECZNY" while badges say "BEZPIECZNY, skuteczny, naturalny"; naming differs between page ("Spray ochronny") and Woo ("SPRAY WSPOMAGAJĄCY OCHRONĘ").
- Neem paragraph misspells "Anticaria gemmatalis" (Anticarsia gemmatalis).
- EN 100ml page Yoast description is in Polish.
### Szampon biała sierść (`kp-szampon-biala-siersc`)
- Category page card for the 1000ml variant is titled "Szampon biała sierść 200ml".
- 1000ml page "PRZEJDŹ DO SKLEPU" links to /produkt/kolor-pielegnacja-szampon-biala-siersc-1000ml/, not the Woo slug /produkt/kolor-pielegnacja-szampon-biala-siersc-dla-psow/ (absent from product sitemap).
- Woo slug pattern inconsistent: 200ml is /produkt/kolorpielegnacja-szampon-biala-siersc-200ml/ (other products use "kolor-pielegnacja-"); 1000ml slug has no capacity.
- Text and INCI identical to Szampon dla maltańczyków (near-duplicate content).
- INCI typos: "Silicone Quaterium-17", "Tocopherol Acetate" (Tocopheryl Acetate).
- EN 1000ml page Yoast description is in Polish.
### Szampon bulldog (`kp-szampon-bulldog`)
- Naming inconsistent: page title "Szampon bulldog", subtitle "Szampon dla bulldogów", Woo "SZAMPON DLA BULLDOGÓW 200ml" (without the Kolor & Pielęgnacja prefix used by other Woo items).
- Woo description lacks Przeznaczenie and Środki ostrożności present on the page; Woo usage differs ("Starannie spłukać letnią wodą i wysuszyć.").
- INCI typos: "Silicone Quaterium-17", truncated "Methylisothiazolinon".
- Related products (DRY&Clean, Fyto-derm) link to /dry&clean-150ml and /fytoderm-100ml; "/dry&clean-150ml" matches no page slug (real page /produkty/dermokosmetyki-weterynaryjne/dryclean-150ml/).
### Szampon ciemna sierść (`kp-szampon-ciemna-siersc`)
- 1000ml page "PRZEJDŹ DO SKLEPU" links to /produkt/kolor-pielegnacja-szampon-ciemna-siersc-1000ml/, not the Woo slug /produkt/kolor-pielegnacja-szampon-ciemna-siersc-dla-psow/ (absent from product sitemap).
- Woo 1000ml Yoast description repeats the same paragraph twice.
- INCI typos: "Glicerin", "Silicone Quaterium-17".
- EN 1000ml page Yoast description is in Polish.
- Related link "/dry&clean-150ml" matches no page slug.
### Szampon dla kotów (`kp-szampon-dla-kotow`)
- INCI typos: "Glicerin", "Silicone Quaterium-17", "Citric Acids".
- No stated functions for the highlighted ingredients (witamina E, olejek z lawendy).
- Related link "/dry&clean-150ml" matches no page slug.
### Szampon łagodzący podrażnienia (`kp-szampon-lagodzacy-podraznienia`)
- Listed on the repellent category page /produkty/preparaty-odstraszajace-pchly-i-kleszcze/ although its own text says "Bez olejku Neem." and makes no flea/tick claim.
- Near-duplicate legacy page /produkty/szampon-lagodzacy-podraznienia-200ml/ (Amiwet packshot, different text) has an identical INCI list.
- Naming inconsistent: title "Szampon łagodzący podrażnienia", H1 "SZAMPON ŁAGODZĄCY OBJAWY PODRAŻNIEŃ SKÓRY", Woo "SZAMPON ŁAGODZĄCY OBJAWY PODRAŻNIENIA 200ml".
- Category cards add "Nadaje połysk i jedwabistość." which is not in the product text.
- INCI typos: "Polisorbate 20", "Junipures Oxycedrus Wood Oil" (Juniperus).
- Related link "/dry&clean-150ml" matches no page slug.
### Szampon dla maltańczyków (`kp-szampon-dla-maltanczykow`)
- Subtitle duplicates a word: "Kolor&Pielęgnacja Szampon SZAMPON DLA psów rasy maltańczyk" (same in Woo short description "SZAMPON SZAMPON").
- Properties, usage and INCI are identical to Szampon biała sierść (near-duplicate content; breed-specific benefit not explained).
- Price inconsistency: 1000ml costs 75.80 PLN (Woo 22009) vs 49.25 PLN for Szampon biała sierść 1000ml (Woo 10954) with an identical INCI list — verify pricing.
- INCI typos: "Silicone Quaterium-17", "Tocopherol Acetate".
- EN 1000ml page Yoast description is in Polish.
### Szampon do mycia na sucho (`kp-szampon-do-mycia-na-sucho`)
- Naming inconsistent: title "Szampon na sucho", H1 "SZAMPON DO MYCIA NA SUCHO", packshot file "pianka_do_mycia_na_sucho".
- INCI typos: "Glicerin", "Perfum" (Parfum), "Citric Acids".
### Szampon nawilżający (`kp-szampon-nawilzajacy`)
- EN 1000ml page Yoast description is in Polish.
### Szampon ochronny przeciw pchłom i kleszczom (`kp-szampon-ochronny-przeciw-pchlom-i-kleszczom`)
- PL Yoast description says "NATURALNIE BEZPIECZNY" while the badge says only "bezpieczny"; Woo Yoast says "działa ochronnie" vs page "wspomaga działanie ochronne".
- Page slug/title "Szampon ochronny" omits what it protects against; Woo name "SZAMPON WSPOMAGAJĄCY OCHRONĘ PRZECIW PCHŁOM I KLESZCZOM".
- Similar legacy page /produkty/szampon-odstraszajacy-pchly-i-kleszcze-200ml/ (Amiwet, Melia Azadirachta Seed Oil, different INCI) — risk of confusion/duplicate intent.
- INCI typo: "GLICERIN"; Neem paragraph misspells "Anticaria gemmatalis".
- Related link "/dry&clean-150ml" matches no page slug.
### Szampon SHIH TZU (`kp-szampon-shih-tzu`)
- Usage typos: "unikniąć" (uniknąć), "rozczesz" (rozczesać).
- INCI typos: double comma "Sodium Hydroxide,,", "Glicerin", "Peg 40".
- "Olejek nabłyszczający" (badge) cannot be matched to a named INCI ingredient.
- EN 1000ml page Yoast description is in Polish.
### Szampon dla szczeniąt (`kp-szampon-dla-szczeniat`)
- Woo usage includes an extra note ("Uwaga: przed kąpielą rozczesz sierść ...") absent from the product page.
- Properties last sentence lacks a final period; INCI double comma "Sodium Hydroxide,,", "Silicone Quaterium-17".
- Similar legacy page /produkty/szampon-dla-szczeniat-200ml/ (Amiwet, different INCI) — risk of confusion.
### Szampon YORK DŁUGI WŁOS (`kp-szampon-york-dlugi-wlos`)
- Usage typos: "unikniąć" (uniknąć), "rozczesz" (rozczesać).
- INCI typos: "Glicerin", "Citric Acids", "Peg 40".
- Related products include Wita-tonyl 30ml (a complementary feed for growth/pregnancy/convalescence) — weak relation to a shampoo.
- EN 1000ml page Yoast description is in Polish.

## oczy-uszy-jama-higiena
### AKARYL-ŻEL (`akaryl-zel`)
- Capacity conflict: page title, H1 and slug say "15ml" (/produkty/produkty-do-pielegnacji-oczu-i-uszu/akaryl-zel-15ml/), but OPAKOWANIE says "25 mil ilość netto", the packshot file is Akaryl-zel_ketokonazol_25ml.png and the only Woo product is 5268 "AKARYL-ŻEL 25ml"; variant recorded as 25 ml. EN page /en/products/eye-and-ear-care/akaryl-zel-15ml-eng/ repeats the 15ml title.
- OPAKOWANIE typo "25 mil ilość netto" (should be "25 ml").
- Shop button links to /produkt/akaryl-zel-25ml (no trailing slash, relies on redirect), so Woo 5268 was not mapped in products.base.json (it sat in woo_without_product_page); mapped here.
- Yoast/OG description says "Emulsja łagodzi podrażnienia" and "Wspomaga zwalczać pasożyty" while the page text says "łagodzi objawy związane z podrażnieniami" and "Pomaga zwalczać pasożyty"; Woo 5268 og:description is the generic "Od 1992 producent i dystrybutor preparatów dla zwierząt. +150 produktów.".
- INCI typos in Skład: "Glicerin" (Glycerin), "Chamomilla Recututa" (Recutita).
- Composition contains Ketoconazole (an antifungal active substance) while the product is presented as a care emulsion with a parasite claim ("Pomaga zwalczać pasożyty przewodu słuchowego zewnętrznego") - regulatory classification/claim wording should be verified.
- Stray widget text "Skuteczny" above "Właściwości" (template leftover; base parser picked it up on other pages as display_name).
- Related-product cards and category tile use root-relative short links (/otiseptol-100ml, /vetaseptol-10ml, /akaryl-zel-15ml) that rely on redirects instead of canonical /produkty/... URLs.
### Ektozin (`ektozin`)
- Yoast/OG description on both PL pages is the Optex text ("Płyn do przemywania oczu i powiek dla psów i kotów. Łagodzi podrażnienia spojówek. Nawilża oczy i powieki. Z wodą chabrową.") - no mention of ektoina and states "dla psów i kotów" while the page says "Dla zwierząt domowych."; both EN pages carry the same Polish description.
- Species inconsistency: page Przeznaczenie and Woo say "Dla zwierząt domowych." / "Zwierząt domowych", but Yoast and the related-product cards on Optex/Otolan pages say "dla psów i kotów"; species kept as on-page.
- Badge typo "Nawilża powierzchnię oka oraz łagodzi stan zapalany" ("zapalny"); also in Woo short descriptions 20416/19290.
- Disease wording on a product described as "dermokosmetyk weterynaryjny": badges "Łagodzi objawy związane z zapaleniem spojówek", "łagodzi stan zapalany"; related card on Optex/Otolan pages goes further: "Łagodzi zapalenie spojówek".
- Środki ostrożności missing on the PL pages; "W przypadku nietolerancji przerwać stosowanie." exists only in Woo 20416/19290 (used here for precautions_verbatim).
- Composition inconsistency: PL pages list "Phosphoric Acid", Woo 19290 (50ml) lists "Ortofosforic Acid".
- products.base.json display_name is lowercase "ektozin" (H1 rendered from lowercase text).
### KROPLE HIALURONOWE DO OCZU I NOSA (`krople-hialuronowe-do-oczu-i-nosa`)
- Wrong Yoast/OG description on both PL pages (and both EN pages): copied Optex/Ektozin text "Płyn do przemywania oczu i powiek dla psów i kotów. Łagodzi podrażnienia spojówek. Nawilża oczy i powieki. Z wodą chabrową." - the product contains no cornflower water.
- 15 ml page slug is a copy artefact: /produkty/produkty-do-pielegnacji-oczu-i-uszu/ektozin-50mlkrople-hialuronowe-do-oczu-i-nosa-15-ml/.
- PL "Sposób stosowania" contains only indication-type text ("Stosować w przypadku mechanicznych podrażnień...") without dosing; dosing "Zakraplać do worka spojówkowego lub nosa po 1-2 kropli od 4 do 6 razy dziennie." exists only in Woo 21831/21841 (field labelled "Zastosowanie"; "1-2 kropli" should read "1-2 krople"). usage_verbatim combines both.
- Środki ostrożności ("W przypadku nietolerancji przerwać stosowanie.") and storage ("Przechowywać w temperaturze +1 oC do +25oC") appear only in Woo, not on the PL pages; "oC" should be "°C".
- Species inconsistency: Przeznaczenie "Dla zwierząt domowych." vs subtitle/properties/Woo "dla psów i kotów".
- INCI typos: "GLICERIN" (GLYCERIN), "SODIUM METHYLOPARABEN" (SODIUM METHYLPARABEN).
- Packshot alt text on PL pages is the whole hyaluronic-acid paragraph (alt attribute misused).
### OPTEX (`optex`)
- Inconsistent capacities: 100 ml page subtitle "Płyn do przemywania oczu i powiek dla psów i kotów" and Przeznaczenie "Dla psów i kotów."; 50 ml page subtitle "Płyn do przemywania oczu i powiek" and Przeznaczenie "Dla zwierząt." (Woo 20411 says "Dla psów i kotów.").
- Shop button on the 100 ml page links to /produkt/optex-100ml (no trailing slash), so Woo 5265 "OPTEX 100ml" was left in woo_without_product_page; mapped here.
- Yoast/OG descriptions ("Łagodzi podrażnienia spojówek", Woo: "łagodzi podrażnienia spojówek") are stronger than the on-page "łagodzi objawy związane z podrażnieniami spojówek"; the same PL description is reused on Ektozin, Optex HA, Krople hialuronowe and the EN 50 ml page.
- INCI typo "Ortofosforic Acid" (Phosphoric/Orthophosphoric Acid).
- Woo 5265 still uses the 2018 packshot (2018/08/Optex_100ml-1-scaled.png) while the page shows 2025/04/Optex_EUROWET-1.png.
- Related card for Ektozin on these pages claims "Łagodzi zapalenie spojówek" (disease claim, stronger than the Ektozin page); Vetaseptol card uses root-relative /vetaseptol-10ml.
- No Środki ostrożności section on page or in Woo.
### OPTEX HA (`optex-ha`)
- Species: PL page Przeznaczenie says only "Dla zwierząt." and the subtitle names no species; "Dla psów i kotów." (species_evidence) comes from Woo 21066 /produkt/optex-ha-50ml/ (PRZEZNACZENIE and short description "PŁYN DO PRZEMYWANIA OCZU I POWIEK DLA PSÓW I KOTÓW").
- Yoast/OG description is the generic Optex text ending "Z wodą chabrową." - no mention of hyaluronic acid (HA) that differentiates the product; Woo Yoast identical to Optex.
- Properties paragraph lacks the final full stop on the PL page; Woo version ends with ". ." (double full stop).
- Claims "łagodzi suchość i ból oczu" and "po zabiegach operacyjnych" go beyond care wording for a "dermokosmetyk weterynaryjny" - verify.
- INCI typo "Ortofosforic Acid".
- EN page slug pattern differs (/optex-ha-50-ml-eng/ vs PL /optex-ha-50ml/) and carries the Polish Yoast description.
### OTISEPTOL (`otiseptol`)
- Woo 5267 short description headline is wrong: "KROPLE OCZYSZCZAJĄCE DO OCZU I NOSA DLA PSÓW I KOTÓW" (copied from Vetaseptol) - product is an ear emulsion.
- Shop button links to /produkt/otiseptol-100ml (no trailing slash), so Woo 5267 was left in woo_without_product_page; mapped here.
- Related-products block on the Otiseptol page links to itself (/otiseptol-100ml) plus root-relative /akaryl-zel-15ml and /vetaseptol-10ml.
- Yoast/OG description says "ułatwia jego usunięcie i szybką regenerację" while the page says "wspomaga szybką regenerację".
- Images inconsistent: page packshot 2026/06/Otiseptol.png, og:image 2019/03/Otiseptol_100ml-3.png, Woo image 2018/08/Otiseptol_100ml_EUROWET.png.
- INCI typo "Glicerin".
- Properties mention "infekcji przewodu słuchowego" (disease context) on a care product.
### OTOLAN (`otolan`)
- Yoast/OG description on PL pages is the pipe-joined badge list plus the full first paragraph (too long); Woo 14292/20413 Yoast concatenates ingredient headings without spaces ("...Olej JojobaNawilża...").
- EN 100 ml page /en/products/eye-and-ear-care/otolan-100ml-eng/ has the Polish Otiseptol description ("Otiseptol ułatwia usunięcie woskowiny..."); EN 50 ml has the Polish Otolan badge list.
- Badge "NATURALNY SKŁAD" while the first INCI ingredient is PARAFFINUM LIQUIDUM (mineral oil) - claim is questionable.
- Sposób stosowania copied from Otiseptol: "usunąć jej nadmiar" ("jej" refers to an emulsion) and then repeats "Nadmiar preparatu usunąć wacikiem.".
- Related card for Ektozin claims "Łagodzi zapalenie spojówek" (unrelated to ear care and stronger than the Ektozin page).
### VETASEPTOL (`vetaseptol`)
- 10 ml shop button points to /produkt/vetaseptol-10ml/ which is not a Woo product; the real Woo product is 5266 /produkt/vetaseptol-krople-10ml/ (left in woo_without_product_page; mapped here).
- Woo 22123 "VETASEPTOL 15ml" has price 0 and is_purchasable=false although in stock - price missing (price_pln set to null).
- Yoast/OG description "Krople łagodzą podrażnienia spojówek..." drops "objawy związane z" used on the page.
- Properties bullet "Oczy – Działa nawilżająco..." does not name the ingredient it describes (only the "Nos" bullet names kwas hialuronowy); double space after "z oka,".
- Category page tile for Vetaseptol 15ml carries an extra link to non-existent /produkty/produkty-do-pielegnacji-oczu-i-uszu/vetaseptol-15ml/ and an http:// link; Vetaseptol tiles elsewhere use root-relative /vetaseptol-10ml.
- Related products on both pages include Stresnal 125ml (calming feed) - unrelated cross-sell; links /optex-100ml and /stresnal-125ml are root-relative.
- Woo 5266 images are 2018 packshots (2018/08/EUROWET_Vetaseptol_10ml_op.png) while the page shows 2026/03/EUROWET_Vetaseptol_10ml_opbutelka.png.
- EN 15 ml page has the Polish Yoast description.
### VETINET higieniczny płyn myjący – niebieski (`vetinet-higieniczny-plyn-myjacy-niebieski`)
- Legacy duplicate pages /produkty/higiena/vetinet-1l/ (3463) and /produkty/higiena/vetinet-5l/ (3467), last modified 2024-02-16: same text and packshots as the "niebieski" pages, old template (stray H1 "Skuteczny" - base parser took it as display_name), Skąpiec/Nokaut price links, shop buttons /produkt/vetinet-1l and /produkt/vetinet-5l that match no Woo product, related tiles DRY&Clean/Aluderm via root-relative links, no inbound links and not in the category listing - should be 301-redirected to the niebieski pages.
- Badge "SUBSTANCJE CZYNNE ZAWARTE W PRODUKCIE SKUTECZNIE ZWALCZAJĄ DROBNOUSTROJE" and properties "Bez podrażnień skóry, błon śluzowych i ran." are biocidal/skin-safety claims - verify BPR status and substantiation.
- Woo names omit the colour ("VETINET higieniczny płyn myjący 1L"/"5L") while pages say "– niebieski –"; category page shows two different products both as "Vetinet 1 L"/"Vetinet 5L".
- INCI typo "GLICERIN".
- Related tile images use old brand file names "Vetiimed_..."/"Vetimed_..." (2020/11, 2024/02).
- EN pages keep Polish titles with "eng/ENG" suffix.
### VETINET higieniczny płyn myjący – fiolet (`vetinet-higieniczny-plyn-myjacy-fiolet`)
- Woo 9606/9607 (lavender) short description and "Argumenty sprzedażowe" say "Z CHLORHEKSYDYNĄ I OLEJKIEM EUKALIPTUSOWYM" - wrong oil; page badge says "z chlorheksydyną i olejkiem z lawendy" and INCI lists LAVANDULA ANGUSTIFOLIA (LAVENDER) OIL.
- Colour/variant naming inconsistent: pages "– fiolet –", Woo "– lawendowy"; category tiles call it just "Vetinet 1 L"/"Vetinet 5L" (same as the blue variant).
- Yoast/OG description identical to the blue variant (no mention of lavender).
- INCI typos "PERFUM" (PARFUM) and "GLICERIN".
- Biocidal claim badge "SUBSTANCJE CZYNNE ZAWARTE W PRODUKCIE SKUTECZNIE ZWALCZAJĄ DROBNOUSTROJE" - verify BPR status.
- Related products on violet pages only point to the blue variant pages (with "Vetiimed_" image file names).
### VETINET higieniczny płyn myjący do powierzchni codziennego użytku – zielony (`vetinet-plyn-do-powierzchni-zielony`)
- Chlorhexidine function quote ("CHLORHEKSYDYNA WYKAZUJE DZIAŁANIE BAKTERIOBÓJCZE I BAKTERIOSTATYCZNE. POSIADA TAKŻE WŁAŚCIWOŚCI GRZYBOBÓJCZE...") exists only in Woo 9608/9609 and their Yoast descriptions, not on the PL pages; it is a biocidal claim - verify BPR authorisation before reuse.
- PL "Sposób stosowania" merges the spray (500 ml) and pour (5 L) instructions without labels ("Spryskać preparatem miejsce przeznaczone do umycia. Nanieść preparat na miejsce przeznaczone do umycia."); Woo text marks them "(500 ml)/(5 l)".
- Badge sets differ: 500 ml page has "PRZYJEMNY ZAPACH", 5 L page has "BEZ ROZCIEŃCZANIA" instead; Yoast description on both pages lists "BEZ ROZCIEŃCZANIA".
- Badge casing typo "pomagają ZWALCZAć".
- No Przeznaczenie section on the pages.
- Woo 9608 is named "higieniczny spray myjący 500ml – eukaliptusowy" while the page says "płyn myjący ... – zielony – 500ml" (colour vs scent naming).
- Composition identical to "VETINET płyn do mycia kuwet, klatek" - two product lines with the same INCI.
- EN pages have a Polish Yoast description from an unrelated product ("Płyn wpływa regulująco na mikroflorę skóry. Ma dobre właściwości myjące, nie powoduje podr...").
### VETINET płyn do mycia kuwet, klatek (`vetinet-plyn-do-mycia-kuwet-klatek`)
- Yoast/OG description typo on both pages: "ELIMINUJE NIEPRZYJEMNE ZAPACHYU | SUWA ZANIECZYSZCZENIA..." (misplaced "U").
- Usage typo "Spryskać preparatem całą powierzchnie" / "Przetrzeć powierzchnie" (should be "powierzchnię"); spray instruction also used for the 5 L canister.
- Woo 9969 (5 L) image is 2021/01/Vetinet_eukaliptus_5L.jpg while the page packshot is 2021/01/Vetinet_plyn_do_kuwet_5L.png - verify the shop image shows the litter-box product.
- Composition identical to the green surface cleaner (VETINET ... do powierzchni codziennego użytku).
- Related products point only to the blue general-purpose VETINET pages.
- EN pages have a Polish Yoast description from an unrelated product ("Płyn wpływa regulująco na mikroflorę skóry...").
### STOMA-ŻEL (`stoma-zel`)
- Shop button on /produkty/pielegnacja-jamy-ustnej-i-zebow/stoma-zel-w-tubce-50g-szczoteczka-do-zebow/ points to /produkt/stoma-zel-50g-szczoteczka/ (no such Woo product); the matching Woo product is 21107 /produkt/stoma-zel-w-tubce-50g-szczoteczka/ (left in woo_without_product_page; mapped here).
- Legacy pages /produkty/pielegnacja-jamy-ustnej-i-zebow/stoma-zel-50g/ (3470, modified 2025-07-30, 2019 packshot) and /stoma-zel-50g-szczoteczka-do-zebow/ (10378, modified 2024-02-26, 2021 packshot): old template (H1 "Skuteczny", Skąpiec/Nokaut links), shop buttons /produkt/stoma-zel-50g and /produkt/stoma-zel-50g-szczoteczka/ match no Woo product, not in the category listing; 10378 is a near-duplicate of 21108 and is still linked from 3470, article /pielegnacja-2/jak-czyscic-zeby/ and post stoma-zel-ze-szczoteczka-do-zebow-nowosc; root-relative /stoma-zel-50g is linked from several pages.
- Woo 21852 (mint 10x50g) description uses the newer beef-style text with therapeutic claims ("Chlorheksydyna wspomaga leczenie stanów zapalnych dziąseł... chroni przed bakteriami przez 8-12 godzin") that the PL page 21917 does not contain.
- Woo 21852 Yoast description ends with "Szczoteczka do mycia zębów wewnątrz opakowania." although the 10x50g carton contains no toothbrush.
- Flavour not stated on pages 3470/10378/21108 although the 21108 packshot is EUROWET_Stoma-zel_mietowy.png; only the carton page says "aromat miętowy".
- Product name spelled inconsistently: "STOMA-ŻEL", "Stoma – żel", "Stoma-żel", "Stoma- żel", "Stoma-Żel".
- Category page shows four different products all titled "Stoma-żel 50g"; related tile typo "2 szczoteczki do pielęgnacji uzębnienia".
- Yoast description identical generic text on all Stoma pages; EN pages keep Polish Yoast descriptions.
### STOMA-ŻEL zapach wołowiny (`stoma-zel-zapach-wolowiny`)
- Composition differs between capacities: 50 g + szczoteczka page/Woo 18371 "AQUA, CALCIUM CARBONATE, CETEARETH-33, ALUMINUM SILICATE, CHLORHEXIDINE DIGLUCONATE, TITANIUM DIOXIDE, PARFUM" vs 10x50g page/Woo 21847 "Aqua, Calcium Carbonate, Cetyl Alcohol, Pumice, Titanium Dioxide, Parfum, Chlorhexidine Digluconate" - verify which formula is current (composition_verbatim holds the 50 g version).
- Therapeutic claims on a cosmetic paste: "Chlorheksydyna wspomaga leczenie stanów zapalnych dziąseł." and "chroni przed bakteriami przez 8-12 godzin" - need substantiation/compliance check; sentence "chroni przed bakteriami..." starts lowercase.
- Badge naming differs: "Zapach wołowiny" (50 g kit) vs "aromat wołowiny" (10x50g carton); double space "z zapachem  wołowiny" on the carton page.
- Woo 21847 Yoast description is the generic mint text ending "Szczoteczka do mycia zębów wewnątrz opakowania." (carton has no brush, not mint).
- Woo 18371 title spacing "zapach wołowiny+ szczoteczka"; EN title for 18378 ends with a non-breaking space.
- Yoast description identical generic text on all Stoma pages; EN pages keep Polish Yoast descriptions.
### Szczoteczki do pielęgnacji uzębienia (`szczoteczki-do-pielegnacji-uzebienia`)
- Skład section shows the STOMA-ŻEL paste INCI ("AQUA, CALCIUM CARBONATE, CETEARETH-33, ALUMINUM SILICATE, CHLORHEXIDINE DIGLUCONATE, TITANIUM DIOXIDE, PARFUM") - copy-paste error for a rubber finger toothbrush; composition_verbatim left null.
- Shop button points to /produkt/stoma-zel-50g-szczoteczka-kopia-szczoteczki-do-pielegnacji-uzebienia-dla-psow-i-kotow/ ("kopia" slug, not a Woo product); real Woo product is 16820 /produkt/stoma-zel-szczoteczki-do-pielegnacji-uzebienia-dla-psow-i-kotow/ (left in woo_without_product_page; mapped here). Woo slug itself starts with "stoma-zel-".
- Disease-prevention claim "zapobiegają chorobom związanym z jamą ustną, takim jak kamień nazębny, zapalenie dziąseł czy infekcje" for a toothbrush.
- Typos: cross-sell line under properties "Sprawdż z pastą Stoma- żel" (Sprawdź, Stoma-żel); related/category tiles "uzębnienia".
- Yoast description (page and Woo) is the entire properties text (~1000 chars) with missing spaces after full stops.
- Related-products block on the page links to itself; cross-sell link to the paste is root-relative /stoma-zel-50g (legacy page).
- "Elastyczne włosie" vs "Gumowe szczoteczki na palec" - description of bristles vs rubber is inconsistent.
### Płyn do higieny jamy ustnej dla psów i kotów (`plyn-do-higieny-jamy-ustnej`)
- OPAKOWANIE says "250g ilość netto" while title and Woo say "250ml" (mass vs volume unit).
- Skład is in feed-label format ("Dodatki technologiczne w 1 kg", "Składniki analityczne w 100 g produktu") but the page never states the legal product category (e.g. karma uzupełniająca / mieszanka paszowa uzupełniająca / kosmetyk) - classification and mandatory feed-label particulars should be verified.
- Composition text starts with a duplicated label ("Skład" heading followed by "Skład: Woda, ...").
- Płyn (250 ml) and Spray (100 ml) have identical composition and identical properties wording apart from "Płyn/Spray".
- Subtitle "Czyste zęby. świeży oddech." - lowercase after full stop.
- Related-products block includes the page itself.
- Woo Yoast description concatenates title and text without space ("...dla psów i kotów 250mlPłyn ..."); EN page Yoast description is the Polish STOMA-ŻEL paste text ("Pasta do pielęgnacji uzębienia...").
### Spray do higieny jamy ustnej dla psów i kotów (`spray-do-higieny-jamy-ustnej`)
- OPAKOWANIE says "100g ilość netto" while title and Woo say "100ml" (mass vs volume unit).
- Skład is in feed-label format ("Dodatki technologiczne w 1 kg", "Składniki analityczne w 100 g produktu") but the page never states the legal product category (e.g. karma uzupełniająca / mieszanka paszowa uzupełniająca / kosmetyk) - classification and mandatory feed-label particulars should be verified.
- Composition text starts with a duplicated label ("Skład" heading followed by "Skład: Woda, ...").
- Płyn (250 ml) and Spray (100 ml) have identical composition and identical properties wording apart from "Płyn/Spray".
- Subtitle "Czyste zęby. świeży oddech." - lowercase after full stop.
- Related-products block includes the page itself.
- Woo Yoast description concatenates title and text without space ("...dla psów i kotów 100mlSpray ..."); EN page Yoast description is the Polish STOMA-ŻEL paste text ("Pasta do pielęgnacji uzębienia...").

## zdrowie-ptaki-inne
### Karbowital (`karbowital`)
- Variant page /produkty/preparaty-wspomagajace-zdrowie/karbowital-125ml/ text differs from /produkty/preparaty-wspomagajace-zdrowie/karbowital-30-tab/; family text taken from the first page.
### Lespewet dla kotów (`lespewet-kot`)
- Variant page /produkty/preparaty-wspomagajace-zdrowie/lespewet-50szt/ text differs from /produkty/preparaty-wspomagajace-zdrowie/lespewet-dla-kotow-60-tab-5-blistrow/; family text taken from the first page.
- Two pack sizes for cats (50 tab. page without shop, 60 tab. in shop) — confirm whether 50 tab. is still sold.
### Metadiar (`metadiar`)
- Variant page /produkty/preparaty-wspomagajace-zdrowie/metadiar-60g/ text differs from /produkty/preparaty-wspomagajace-zdrowie/metadiar-50g/; family text taken from the first page.
### Zn-Cynk (`zn-cynk`)
- WooCommerce product 8530 is assigned to dermokosmetyki-weterynaryjne; its page is under preparaty-wspomagajace-zdrowie (category to correct). Shop button on the page points to non-existent /produkt/zn-cynk_60tab/.
### Floramue (`floramue`)
- Badge on the page reads 'PRIÓRA' (typo for 'PIÓRA').
### Floratonyl (`floratonyl`)
- Packshot file name is a generic template name (produkty_2000px_…png) — confirm the image shows Floratonyl.
