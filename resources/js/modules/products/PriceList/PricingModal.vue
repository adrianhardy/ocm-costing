<template>
    <b-modal id="pricing" :title="product.name" ref="modal" ok-title="Save" @ok="save($event)" cancel-title="Close"
             ok-title-html="Update" centered size="xl">
        <div>
            <div class="row">
                <div class="col-12 col-md-3">
                    <h5>Product</h5>
                    <table class="table table-sm small">
                        <tr>
                            <th class="w-50">Item Number</th>
                            <td>{{ value.product.item_number }}</td>
                        </tr>
                        <tr>
                            <th class="w-50">Primary SKU</th>
                            <td>{{ value.product.primary_sku }}</td>
                        </tr>
                        <tr>
                            <th class="w-50">Primary ASIN</th>
                            <td>{{ value.product.primary_asin }}</td>
                        </tr>
                    </table>
                    <h5>Postage</h5>
                    <table class="table table-sm small">
                        <tr>
                            <th class="w-50">UK Postage Method</th>
                            <td>{{ ukPostageMethod }}</td>
                        </tr>
                        <tr>
                            <th class="w-50">EU Postage Method</th>
                            <td>{{ euPostageMethod }}</td>
                        </tr>
                        <tr>
                            <th class="w-50">RoW Postage Method</th>
                            <td>{{ rowPostageMethod }}</td>
                        </tr>
                    </table>
                    <h5>Carton</h5>
                    <table class="table table-sm small">
                        <tr>
                            <th class="w-50">Units per carton</th>
                            <td>{{ line.units_per_case }}</td>
                        </tr>
                        <tr>
                            <th>Inner quantity</th>
                            <td>{{ line.inner_qty }}</td>
                        </tr>
                        <tr>
                            <th>Weight (kg)</th>
                            <td>{{ line.case_weight_kg }}</td>
                        </tr>
                        <tr>
                            <th>Dimensions (LxWxH)</th>
                            <td>{{ line.length_cm }} x
                                {{ line.width_cm }} x
                                {{ line.height_cm }} cm
                            </td>
                        </tr>
                    </table>
                    <h5>Unit</h5>
                    <table class="table table-sm small">
                        <b-tr>
                            <th class="w-50">Weight (kg)</th>
                            <td>{{ line.unit_weight_kg }}</td>
                        </b-tr>
                        <b-tr>
                            <th>Dimensions (LxWxH)</th>
                            <td>{{ line.unit_length_cm }} x
                                {{ line.unit_width_cm }} x
                                {{ line.unit_height_cm }} cm
                            </td>
                        </b-tr>
                    </table>
                    <div>
                    </div>
                    <h5>Image</h5>
                    <div class="p-1">
                        <b-img fluid style="max-height: 200px" class="img-thumnail"
                               :src="value.product | product_image_url"/>
                    </div>
                </div>
                <div class="col-12 col-md-9">
                    <b-tabs content-class="pt-3">
                        <b-tab title="Pricing">
                            <h5>Pricing</h5>
                            <div class="row">
                                <div class="col-4">
                                    <b-form-group label="Est. L' Cost" label-cols="4" label-size="sm">
                                        <b-input-group prepend="£" size="sm">
                                            <b-form-input readonly v-model="value.est_landed_cost_per_unit_gbp"/>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Referral Fee" label-cols="4" label-size="sm">
                                        <b-input-group size="sm" class="w-70">
                                            <b-form-input v-model="value.marketplace_commission_pc"/>
                                            <b-input-group-append class="w-30">
                                                <b-input-group-text class="w-100 text-center">
                                                    %
                                                </b-input-group-text>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="EU Rate" label-cols="4" label-size="sm">
                                        <b-input-group size="sm">
                                            <b-form-input v-model.number="value.eur_fx_rate"/>
                                            <b-input-group-append class="w-30">
                                                <b-input-group-text class="w-100">
                                                    : £1
                                                </b-input-group-text>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="overflow-auto">
                                <div class="row flex-row flex-nowrap">
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="UK"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            fee-field="unit_uk_fba_fee"
                                                            sf-price-field="unit_sf_uk_price"
                                                            ship-field="unit_shipping_gbp" price-field="unit_price_gbp"
                                                            vat-field="uk_vat"
                                                            :fx-rate="1"
                                                            @copy-sf-price="copySfPrice()"
                                        />
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="FR"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            sf-price-field="unit_sf_fr_price"
                                                            fee-field="unit_fr_fba_fee"
                                                            ship-field="unit_fr_shipping" price-field="unit_fr_price"
                                                            vat-field="fr_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="DE"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            sf-price-field="unit_sf_de_price"
                                                            fee-field="unit_de_fba_fee"
                                                            ship-field="unit_de_shipping" price-field="unit_de_price"
                                                            vat-field="de_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="IT"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            fee-field="unit_it_fba_fee"
                                                            sf-price-field="unit_sf_it_price"
                                                            ship-field="unit_it_shipping" price-field="unit_it_price"
                                                            vat-field="it_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="ES"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            fee-field="unit_es_fba_fee"
                                                            sf-price-field="unit_sf_es_price"
                                                            ship-field="unit_es_shipping" price-field="unit_es_price"
                                                            vat-field="es_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="NL"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            sf-price-field="unit_sf_nl_price"
                                                            fee-field="unit_nl_fba_fee"
                                                            ship-field="unit_nl_shipping" price-field="unit_nl_price"
                                                            vat-field="nl_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <rest-of-world-product-pricing v-model="value" currency="SEK"
                                           :marketplace-commission-pc="value.marketplace_commission_pc"
                                           sf-price-field="unit_sf_se_price"
                                           ship-field="unit_se_shipping"
                                           fee-field="unit_se_fba_fee"
                                           price-field="unit_se_price"
                                           fx-field="sek_fx_rate"
                                           vat-field="se_vat"
                                        />
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <rest-of-world-product-pricing v-model="value" currency="PLN"
                                                                       :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                       sf-price-field="unit_sf_pln_price"
                                                                       ship-field="unit_pln_shipping"
                                                                       fee-field="unit_pln_fba_fee"
                                                                       price-field="unit_pln_price"
                                                                       fx-field="pln_fx_rate"
                                                                       vat-field="pl_vat"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <rest-of-world-product-pricing v-model="value" currency="USD"
                                                                   :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                   sf-price-field="unit_sf_usd_price"
                                                                   ship-field="unit_usd_shipping"
                                                                   fee-field="unit_usd_fba_fee"
                                                                   price-field="unit_usd_price" fx-field="usd_fx_rate"/>
                                </div>
                                <div class="col-12 col-md-3">
                                    <rest-of-world-product-pricing v-model="value" currency="CAD"
                                                                   :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                   sf-price-field="unit_sf_cad_price"
                                                                   fee-field="unit_cad_fba_fee"
                                                                   ship-field="unit_cad_shipping"
                                                                   price-field="unit_cad_price" fx-field="cad_fx_rate"/>
                                </div>
                                <div class="col-12 col-md-3">
                                    <rest-of-world-product-pricing v-model="value" currency="MXN"
                                                                   :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                   sf-price-field="unit_sf_mxn_price"
                                                                   fee-field="unit_mxn_fba_fee"
                                                                   ship-field="unit_mxn_shipping"
                                                                   price-field="unit_mxn_price" fx-field="mxn_fx_rate"/>
                                </div>
                                <div class="col-12 col-md-3">
                                    <rest-of-world-product-pricing v-model="value" currency="AUD"
                                                                   :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                   sf-price-field="unit_sf_aud_price"
                                                                   ship-field="unit_aud_shipping"
                                                                   fee-field="unit_aud_fba_fee"
                                                                   price-field="unit_aud_price" fx-field="aud_fx_rate"/>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab title="History"> <!-- History Tab -->
                            <b-table size="sm" ref="table" :fields="fields" :items="items">
                                <template #cell(created_at)="data">
                                    {{ data.value | date_dmY }}
                                </template>
                                <template #cell(approved_at)="data">
                                    {{ data.value | date_dmY }}
                                </template>
                            </b-table>
                        </b-tab>
                        <b-tab title="Amazon shipping rates">
                            <b-table-simple striped sticky-header="400px">
                                <b-thead>
                                    <b-tr>
                                        <b-th scope="col"></b-th>
                                        <b-th scope="col">UK (£)</b-th>
                                        <b-th scope="col">DE/PL/CZ (&#128;)</b-th>
                                        <b-th scope="col">DE only (&#128;)</b-th>
                                        <b-th scope="col">FR (&#128;)</b-th>
                                        <b-th scope="col">IT (&#128;)</b-th>
                                        <b-th scope="col">ES (&#128;)</b-th>
                                        <b-th scope="col">NL (&#128;)</b-th>
                                        <b-th scope="col">SE (KR)</b-th>
                                        <b-th scope="col">PL (zł)</b-th>

                                    </b-tr>
                                </b-thead>
                                <b-tbody>
                                    <b-tr class="table-active">
                                        <b-th colspan="10">Small Envelope: Dimensions &#8804; 20 x 15 x 1cm</b-th>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 80g</b-th>
                                        <b-td>£1.46</b-td>
                                        <b-td>&#128;1.73</b-td>
                                        <b-td>&#128;2.08</b-td>
                                        <b-td>&#128;2.37</b-td>
                                        <b-td>&#128;2.74</b-td>
                                        <b-td>&#128;2.26</b-td>
                                        <b-td>&#128;1.62</b-td>
                                        <b-td>25.79kr</b-td>
                                        <b-td>4.48zł</b-td>
                                    </b-tr>
                                    <b-tr class="table-active">
                                        <b-td colspan="10">Standard Envelope: Dimensions &#8804; 33 x 23 x 2.5cm</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 60g</b-th>
                                        <b-td>£1.56</b-td>
                                        <b-td>&#128;1.88</b-td>
                                        <b-td>&#128;2.23</b-td>
                                        <b-td>&#128;2.52</b-td>
                                        <b-td>&#128;2.88</b-td>
                                        <b-td>&#128;2.51</b-td>
                                        <b-td>&#128;1.76</b-td>
                                        <b-td>26.49kr</b-td>
                                        <b-td>4.53zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 210g</b-th>
                                        <b-td>£1.66</b-td>
                                        <b-td>&#128;2.01</b-td>
                                        <b-td>&#128;2.36</b-td>
                                        <b-td>&#128;3.01</b-td>
                                        <b-td>&#128;3.03</b-td>
                                        <b-td>&#128;2.81</b-td>
                                        <b-td>&#128;1.93</b-td>
                                        <b-td>27.64kr</b-td>
                                        <b-td>4.66zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 460g</b-th>
                                        <b-td>£1.77</b-td>
                                        <b-td>&#128;2.16</b-td>
                                        <b-td>&#128;2.51</b-td>
                                        <b-td>&#128;3.38</b-td>
                                        <b-td>&#128;3.27</b-td>
                                        <b-td>&#128;3.02</b-td>
                                        <b-td>&#128;2.05</b-td>
                                        <b-td>31.84kr</b-td>
                                        <b-td>4.91zł</b-td>
                                    </b-tr>
                                    <b-tr class="table-active">
                                        <b-td colspan="10">Large Envelope: Dimensions &#8804; 33 x 23 x 5cm</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 960g</b-th>
                                        <b-td>£2.21</b-td>
                                        <b-td>&#128;2.52</b-td>
                                        <b-td>&#128;2.87</b-td>
                                        <b-td>&#128;3.96</b-td>
                                        <b-td>&#128;3.53</b-td>
                                        <b-td>&#128;3.15</b-td>
                                        <b-td>&#128;2.48</b-td>
                                        <b-td>33.09kr</b-td>
                                        <b-td>5.64zł</b-td>
                                    </b-tr>
                                    <b-tr class="table-active">
                                        <b-td colspan="10">Standard parcel: Dimensions &#8804; 45 x 34 x 26cm</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 150g</b-th>
                                        <b-td>£2.19</b-td>
                                        <b-td>&#128;2.66</b-td>
                                        <b-td>&#128;3.01</b-td>
                                        <b-td>&#128;3.95</b-td>
                                        <b-td>&#128;3.62</b-td>
                                        <b-td>&#128;2.99</b-td>
                                        <b-td>&#128;2.53</b-td>
                                        <b-td>37.14kr</b-td>
                                        <b-td>5.35zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 400g</b-th>
                                        <b-td>£2.33</b-td>
                                        <b-td>&#128;2.95</b-td>
                                        <b-td>&#128;3.30</b-td>
                                        <b-td>&#128;4.72</b-td>
                                        <b-td>&#128;4.04</b-td>
                                        <b-td>&#128;3.50</b-td>
                                        <b-td>&#128;2.81</b-td>
                                        <b-td>38.91kr</b-td>
                                        <b-td>5.66zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 900g</b-th>
                                        <b-td>£2.50</b-td>
                                        <b-td>&#128;3.38</b-td>
                                        <b-td>&#128;3.73</b-td>
                                        <b-td>&#128;5.46</b-td>
                                        <b-td>&#128;4.66</b-td>
                                        <b-td>&#128;3.78</b-td>
                                        <b-td>&#128;3.26</b-td>
                                        <b-td>39.45kr</b-td>
                                        <b-td>6.48zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 1.4kg</b-th>
                                        <b-td>£2.66</b-td>
                                        <b-td>&#128;3.99</b-td>
                                        <b-td>&#128;4.34</b-td>
                                        <b-td>&#128;5.68</b-td>
                                        <b-td>&#128;5.24</b-td>
                                        <b-td>&#128;4.25</b-td>
                                        <b-td>&#128;3.84</b-td>
                                        <b-td>40.62kr</b-td>
                                        <b-td>6.75zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 1.9kg</b-th>
                                        <b-td>£2.91</b-td>
                                        <b-td>&#128;4.30</b-td>
                                        <b-td>&#128;4.65</b-td>
                                        <b-td>&#128;5.74</b-td>
                                        <b-td>&#128;5.47</b-td>
                                        <b-td>&#128;4.32</b-td>
                                        <b-td>&#128;4.11</b-td>
                                        <b-td>41.99kr</b-td>
                                        <b-td>6.75zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 2.9kg</b-th>
                                        <b-td>£4.14</b-td>
                                        <b-td>&#128;4.77</b-td>
                                        <b-td>&#128;5.12</b-td>
                                        <b-td>&#128;7.45</b-td>
                                        <b-td>&#128;6.10</b-td>
                                        <b-td>&#128;4.91</b-td>
                                        <b-td>&#128;4.69</b-td>
                                        <b-td>42.94kr</b-td>
                                        <b-td>6.83zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 3.9kg</b-th>
                                        <b-td>£4.53</b-td>
                                        <b-td>&#128;5.35</b-td>
                                        <b-td>&#128;5.70</b-td>
                                        <b-td>&#128;7.92</b-td>
                                        <b-td>&#128;6.82</b-td>
                                        <b-td>&#128;6.02</b-td>
                                        <b-td>&#128;4.71</b-td>
                                        <b-td>44.21kr</b-td>
                                        <b-td>6.86zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 4.9kg</b-th>
                                        <b-td>£4.62</b-td>
                                        <b-td>&#128;5.52</b-td>
                                        <b-td>&#128;5.87</b-td>
                                        <b-td>&#128;7.95</b-td>
                                        <b-td>&#128;7.24</b-td>
                                        <b-td>&#128;6.20</b-td>
                                        <b-td>&#128;4.72</b-td>
                                        <b-td>46.39kr</b-td>
                                        <b-td>6.96zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 5.9kg</b-th>
                                        <b-td>£5.12</b-td>
                                        <b-td>&#128;5.95</b-td>
                                        <b-td>&#128;6.30</b-td>
                                        <b-td>&#128;8.63</b-td>
                                        <b-td>&#128;8.07</b-td>
                                        <b-td>&#128;6.52</b-td>
                                        <b-td>&#128;4.99</b-td>
                                        <b-td>47.86kr</b-td>
                                        <b-td>7.01zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 6.9kg</b-th>
                                        <b-td>£5.28</b-td>
                                        <b-td>&#128;5.95</b-td>
                                        <b-td>&#128;6.30</b-td>
                                        <b-td>&#128;8.63</b-td>
                                        <b-td>&#128;8.08</b-td>
                                        <b-td>&#128;6.64</b-td>
                                        <b-td>&#128;5.05</b-td>
                                        <b-td>48.31kr</b-td>
                                        <b-td>7.06zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 7.9kg</b-th>
                                        <b-td>£5.42</b-td>
                                        <b-td>&#128;6.11</b-td>
                                        <b-td>&#128;6.46</b-td>
                                        <b-td>&#128;9.42</b-td>
                                        <b-td>&#128;8.34</b-td>
                                        <b-td>&#128;7.12</b-td>
                                        <b-td>&#128;5.10</b-td>
                                        <b-td>52.76kr</b-td>
                                        <b-td>7.36zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 8.9kg</b-th>
                                        <b-td>£5.47</b-td>
                                        <b-td>&#128;6.11</b-td>
                                        <b-td>&#128;6.46</b-td>
                                        <b-td>&#128;9.42</b-td>
                                        <b-td>&#128;8.37</b-td>
                                        <b-td>&#128;7.12</b-td>
                                        <b-td>&#128;5.16</b-td>
                                        <b-td>53.80kr</b-td>
                                        <b-td>7.39zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 9.9kg</b-th>
                                        <b-td>£6.23</b-td>
                                        <b-td>&#128;6.39</b-td>
                                        <b-td>&#128;6.74</b-td>
                                        <b-td>&#128;9.41</b-td>
                                        <b-td>&#128;8.82</b-td>
                                        <b-td>&#128;7.12</b-td>
                                        <b-td>&#128;5.31</b-td>
                                        <b-td>62.45kr</b-td>
                                        <b-td>8.78zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 10.9kg</b-th>
                                        <b-td>£6.24</b-td>
                                        <b-td>&#128;6.40</b-td>
                                        <b-td>&#128;6.75</b-td>
                                        <b-td>&#128;9.48</b-td>
                                        <b-td>&#128;8.85</b-td>
                                        <b-td>&#128;7.12</b-td>
                                        <b-td>&#128;5.49</b-td>
                                        <b-td>63.37kr</b-td>
                                        <b-td>9.09zł</b-td>
                                    </b-tr>
                                    <b-tr>
                                        <b-th scope="row">&#8804; 11,9kg</b-th>
                                        <b-td>£6.30</b-td>
                                        <b-td>&#128;6.61</b-td>
                                        <b-td>&#128;6.96</b-td>
                                        <b-td>&#128;9.49</b-td>
                                        <b-td>&#128;8.89</b-td>
                                        <b-td>&#128;7.13</b-td>
                                        <b-td>&#128;5.50</b-td>
                                        <b-td>64.19kr</b-td>
                                        <b-td>9.12zł</b-td>
                                    </b-tr>
                                </b-tbody>
                            </b-table-simple>
                        </b-tab>
                    </b-tabs>
                </div>
            </div>
        </div>
    </b-modal>
</template>

<script>
import RestOfWorldProductPricing from "./RestOfWorldProductPricing";
import EuOfWorldProductPricing from "./EuProductPricing";
import EuProductPricing from "./EuProductPricing";

export default {
    name: "PricingModal",
    components: {EuProductPricing, EuOfWorldProductPricing, RestOfWorldProductPricing},
    computed: {
        ukPostageMethod() {
            if (this.value.product.uk_postage_method) {
                return this.value.product.uk_postage_method.toUpperCase().replaceAll('_', ' ');
            }
            return 'NOT SET';
        },
        euPostageMethod() {
            if (this.value.product.eu_postage_method) {
                return this.value.product.eu_postage_method.toUpperCase().replaceAll('_', ' ');
            }
            return 'NOT SET';
        },
        rowPostageMethod() {
            if (this.value.product.row_postage_method) {
                return this.value.product.row_postage_method.toUpperCase().replaceAll('_', ' ');
            }
            return 'NOT SET';
        },
        product() {
            if (this.value.product) {
                return this.value.product;
            }
            return {};
        },
        line() {
            if (this.value.sales_order_line) {
                return this.value.sales_order_line;
            }
            return {};
        }
    },
    methods: {
        copySfPrice() {
            const euPrice = (this.value['unit_sf_uk_price'] / this.value['eur_fx_rate']).toFixed(2);
            this.value['unit_sf_fr_price'] = euPrice;
            this.value['unit_sf_es_price'] = euPrice;
            this.value['unit_sf_it_price'] = euPrice;
            this.value['unit_sf_de_price'] = euPrice;
            this.value['unit_sf_nl_price'] = euPrice;

            this.value['unit_sf_se_price'] = (this.value['unit_sf_uk_price'] / this.value['sek_fx_rate']).toFixed(2);
            this.value['unit_sf_pln_price'] = (this.value['unit_sf_uk_price'] / this.value['pln_fx_rate']).toFixed(2);
            this.value['unit_sf_aud_price'] = (this.value['unit_sf_uk_price'] / this.value['aud_fx_rate']).toFixed(2);
            this.value['unit_sf_mxn_price'] = (this.value['unit_sf_uk_price'] / this.value['mxn_fx_rate']).toFixed(2);
            this.value['unit_sf_usd_price'] = (this.value['unit_sf_uk_price'] / this.value['usd_fx_rate']).toFixed(2);
            this.value['unit_sf_cad_price'] = (this.value['unit_sf_uk_price'] / this.value['cad_fx_rate']).toFixed(2);

        },
        save(event) {
            event.preventDefault();
            this.update()
        },
        update() {
            this.$http.put(`/api/pricing/${this.value.id}`, {...this.value}, {
                params: {
                    include: `product,product.media,sales_order_line`
                }
            }).then(response => {
                this.value = response.data;
                this.$emit('updated');
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
            })
        },
        open(line) {
            this.value = line;
            this.$http.get(`/api/pricing/${this.value.id}`, {
                params: {
                    include: `product,product.media,sales_order_line`
                }
            }).then(response => {
                this.value = response.data
                this.$refs['modal'].show();
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
            })
        },
        items(ctx) {
            return this.$paginatedFetch(
                `api/pricing/${this.value.id}/history`,
                this.pagination,
                ctx, {
                    include: 'approver'
                });
        },
        close() {
            this.$refs['modal'].hide();
        }
    },
    data() {
        return {
            value: {},
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
                sortBy: 'approved_at'
            },
            fields: [
                {key: 'approved_at', label: 'Approved At', sortable: true},
                {key: 'approver.name', label: 'Approved By', sortable: true},
                {key: 'unit_price_gbp', label: 'Unit Price'},
                {key: 'margin_pc', label: 'Margin'},
            ]
        }
    }
}
</script>

<style scoped>
::v-deep .modal-xlg {
    max-width: 1200px !important;
}
</style>
