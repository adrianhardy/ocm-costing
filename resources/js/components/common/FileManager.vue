<template>
  <div>
    <b-table-simple :sticky-header="maxHeight">
      <b-thead>
        <b-tr>
          <b-th>Created</b-th>
          <b-th>Filename</b-th>
          <b-th class="w-1"></b-th>
        </b-tr>
      </b-thead>
      <b-tbody>
        <b-tr v-if="!files || files.length < 1">
          <b-td colspan="2" class="text-center">No files added</b-td>
        </b-tr>
        <b-tr v-for="file in files" :key="files.id">
          <b-td>{{ new Date(file.created_at) | date('dd/MM/yy HH:mm') }}</b-td>
          <b-td>
            <a :href="file.url">
              {{ file.name }}
            </a>
          </b-td>
          <b-td>
            <b-button variant="outline-danger" size="sm" @click="trash(file)">
              <b-icon-x class="text-danger" />
            </b-button>
          </b-td>
        </b-tr>
      </b-tbody>
    </b-table-simple>
    <hr/>
    <b-input-group>
      <b-form-file accept=".jpg, .png, .gif, .doc, .docx, .bmp, .pdf" v-model="file"/>
      <b-button variant="outline-primary float-right" @click="upload">Upload</b-button>
    </b-input-group>
  </div>
</template>


<script>
import {dateFilter} from "vue-date-fns";

export default {
  props: {
    propName: {
      type: String,
      default: 'files'
    },
    addUrl: String,
    indexUrl: String,
    maxHeight: {
      type: String,
      default: '200px'
    }
  },
  filters: {
    date: dateFilter
  },
  name: "FileManager",
  data() {
    return {
      file: null,
      files: []
    }
  },
  mounted() {
    this.refresh();
  },
  methods: {
    async trash(media) {
      if (await this.$bvModal.msgBoxConfirm("Are you sure you wnt to delete this file?")) {
        this.$http.delete(media.url).then(response => {
          this.refresh();
        }).catch(error => {
          this.$handleExceptionResponse(error, true);
        });
      }
    },
    refresh() {
      this.$http.get(this.indexUrl).then(response => {
        this.files = response.data.items;
      }).catch(error => {
        console.log(error);
        this.$handleExceptionResponse(error, true);
      })
    },
    upload() {
      const file = new FormData();
      file.append('file', this.file);
      file.append('filename', this.file.name);

      const url = this.addUrl;
      const loader = this.$loading.show();
      this.$http.post(url, file,
          {headers: {'Content-Type': 'multipart/form-data'}}
      ).then(response => {
        this.file = null;
        loader.hide();
        this.$emit('updated', response.data);
        this.refresh();
        this.$bvToast.toast("File saved", {variant: "success"});
      }).catch(error => {
        loader.hide();
        this.$handleExceptionResponse(error, true);
      })
    }
  }
}
</script>

<style scoped>

</style>
