<template>
  <div>
    <back-button></back-button>
    <div class="s-area has-background-light">
      <div class="tabs is-centered">
        <ul>
          <li><router-link v-bind:to="'/search/freeword/' + this.query_text"><i class="far fa-images"></i></router-link></li>
          <li><router-link v-bind:to="'/search/user/' + this.query_text"><i class="fas fa-users"></i></router-link></li>
          <li class="is-active"><router-link v-bind:to="'/search/location/' + this.query_text"><i class="fas fa-map-marker"></i></router-link></li>
          <li><router-link v-bind:to="'/search/tag/' + this.query_text"><i class="fas fa-tags"></i></router-link></li>
          <li><router-link v-bind:to="'/search/lenscamera/' + this.query_text"><i class="fas fa-camera"></i><i class="far fa-dot-circle"></i></router-link></li>
        </ul>
      </div>
      <div class="field has-addons">
        <div class="control is-expanded">
          <input class="input" type="text" v-model:value="query_text" v-on:keyup.enter="fw_search" placeholder="検索..">
        </div>
        <div class="control">
          <a class="button is-info" v-on:click="fw_search">
            <i class="fas fa-search"></i>
          </a>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="photoarea">
        <thumb-component v-for="photo in photo_list.data" :photo="photo" :key="photo.p_id"></thumb-component>
      </div>
    </div>
    <div class="container">
      <p class="has-text-centered">{{ photo_list.current_page }} / {{ photo_list.last_page }}</p>
      <nav class="pagination is-centered" role="navigation" aria-label="pagination">
        <a class="pagination-previous" v-if="page!=1" v-on:click="change_page(-1)">前ページ</a>
        <a class="pagination-previous" v-if="page==1" disabled>前ページ</a>
        <a class="pagination-next" v-if="page!=photo_list.last_page" v-on:click="change_page(1)">次ページ</a>
        <a class="pagination-next" v-if="page==photo_list.last_page" disabled>次ページ</a>
      </nav>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

import BackButton from '../parts/BackButton.vue';
import ThumbComponent from '../parts/Thumbnail.vue';

export default {
  data() {
    return {
      photo_list: [],
      query_text: "",
      page: 1,
      loading: false
    };
  },
  methods: {
    fw_search: function() {
      this.$router.push('/search/location/' + encodeURIComponent(this.query_text));
    },
    created_method:async function(query_text) {
      try {
        let res = await axios.get('/api/search/location?words=' + encodeURIComponent(query_text) + '&page=' + this.page);
        this.photo_list = res.data;
      } catch (e) {
        console.error(e)
      }
    },
    change_page(amount) {
      this.page = this.page + amount;
      this.created_method(this.query_text);
    }
  },
  components: {
    'back-button': BackButton,
    'thumb-component': ThumbComponent
  },
  async created() {
    this.query_text = this.$route.params.words;
    this.created_method(this.query_text);
  },
  watch: {
    '$route' (to, from) {
      this.created_method(to.params.words);
    }
  }
}


</script>

<style scoped>
.s-area {
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}
/*tab*/
.is_selected {
  border-bottom: solid 3px hsl(217, 71%, 53%);
}

.tab-contents {
  margin-top: 1rem;
}

.photoarea {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

.photo {
  height: 150px;
  width: 150px;
  background-color: hsl(217, 71%, 53%);
  background-image: url("/storage/photo/1.JPG");
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  text-align: center;
  margin: 1rem;
  color: white;
  line-height: 150px;
}

.is_round img{
  border-radius: 100px;
}
</style>
