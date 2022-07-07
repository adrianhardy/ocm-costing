// import App from "./mobile/App";

require('./bootstrap');
require('jquery');
require('popper.js');
require('bootstrap');

import Vue from 'vue';
import VueDateFns from "vue-date-fns";
import VueSelect from 'vue-select';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import PaginationPlugin from "./plugins/PaginationPlugin";
import HandleExceptionResponse from "./plugins/HandleExceptionResponse";
import MoneyFilters from './plugins/MoneyFilters';
import ProductFilters from './plugins/ProductFilters';
import TextFilters from './plugins/TextFilters';
import VuePapaParse from 'vue-papa-parse';
import vueDebounce from 'vue-debounce';
import VueSmoothScroll from 'vue2-smooth-scroll';
import VueRouter from 'vue-router';


import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

Vue.prototype.$http = window.axios;
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(VuePapaParse);
Vue.use(VueDateFns);
Vue.use(PaginationPlugin);
Vue.use(MoneyFilters);
Vue.use(TextFilters);
Vue.use(ProductFilters);
Vue.use(HandleExceptionResponse);
Vue.use(vueDebounce);
Vue.use(Loading);
Vue.use(VueSmoothScroll, {
    offset: -100
});
Vue.use(VueRouter);

Vue.use(require('./plugins/DateFilter').default);

Vue.component('quick-cost-modal', require('./components/QuickCostModal').default);
Vue.component('vue-select', VueSelect);
Vue.component('auto-toast', require('./components/AutoToast.vue').default);
Vue.component('create-import', require('./modules/imports/Importer.vue').default);
Vue.component('products-list', require('./modules/products/ProductsList.vue').default);
Vue.component('edit-product', require('./modules/products/EditProduct.vue').default);
Vue.component('users-list', require('./modules/users/UserList.vue').default);
Vue.component('consignments', require('./modules/sales-orders/consignments/ConsignmentsList.vue').default);
Vue.component('edit-user', require('./modules/users/EditUser.vue').default);
Vue.component('suppliers-list', require('./modules/suppliers/SuppliersList.vue').default);
Vue.component('edit-supplier', require('./modules/suppliers/EditSupplier.vue').default);
Vue.component('factories-list', require('./modules/factories/FactoryList.vue').default);
Vue.component('currency-list', require('./modules/currencies/CurrencyList.vue').default);
Vue.component('country-list', require('./modules/countries/CountryList.vue').default);
Vue.component('price-list', require('./modules/products/PriceList/PriceList').default);

Vue.component('sales-orders-list', require('./modules/sales-orders/SalesOrdersList.vue').default);
Vue.component('edit-sales-order', require('./modules/sales-orders/EditSalesOrder.vue').default);
Vue.component('edit-inspection-report-modal', require('./modules/inspection/EditInspectionReportModal').default);
Vue.component('inspection-report-list', require('./modules/inspection/InspectionReportList').default);
Vue.component('shipments-list', require('./modules/shipments/ShipmentsList').default);
Vue.component('edit-shipment', require('./modules/shipments/EditShipment').default);

Vue.component('confirm-navigation-modal', require('./components/ConfirmNavigationModal').default);
// make available to the footer scripts which are used
// for raising toasts, etc

Vue.component('settings-list', require('./modules/settings/SettingsList.vue').default);
Vue.component('amazon-guidance-upload', require('./modules/settings/AmazonGuidanceUpload').default)

Vue.component('unit-dimensions-import', require('./modules/imports/UnitDimensionsImport').default)
Vue.component('products-import', require('./modules/imports/ProductsImport').default);

Vue.component('create-currency', require('./modules/currencies/CreateCurrency.vue').default);

window.Vue = Vue;

const router = new VueRouter({
    mode: 'history',
    base: __dirname,
});

new Vue({router}).$mount('#app');

document.querySelectorAll('.custom-file-input').forEach(element => {
    element.addEventListener('change',function(e) {
        if (e.target.previousElementSibling) {
            e.target.previousElementSibling.innerText = e.target.files[0].name;
        }
    });
});
