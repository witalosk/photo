<template>
  <div>
    <transition name="fadeup">
      <div class="modal" v-if="showModal" ref="modal">
        <div class="slidearea has-background-black-ter has-text-light" ref="modalbar" v-on:click="modalToggle()" v-on:touchstart="t_start" v-on:touchmove="t_move" v-on:touchend="t_end">
          <i class="fas fa-chevron-down fa-lg"></i>
        </div>
        <div class="scrollarea">
          <detail-content v-bind:photo="photo" v-on:toggleLike="likeToggleData()"></detail-content>
        </div>
      </div>
    </transition>
    <div class="post">
      <div class="post-header">
        <div class="post-header-left is-size-7">
          <p><router-link v-bind:to="'/user/' + photo.screen_name"><img v-bind:src="photo.icon"></router-link></p>
          <p>
            <router-link v-bind:to="'/user/' + photo.screen_name"><strong>{{ photo.name }}</strong></router-link>(@{{photo.screen_name}})
            <br><span v-if="photo.p_location" class="has-text-grey is-size-8">{{ photo.location_name ? photo.location_name : photo.p_location }}</span>
          </p>
        </div>
      </div>
      <div class="post-contents" style="margin: 0 auto; cursor: pointer;" v-on:click="modalToggle()">
        <img v-bind:src="'/storage/' + photo.path" >
      </div>
      <div class="post-footer">
        <p class="post-title" v-on:click="showModal=true"><strong>{{ photo.title }}</strong></p>
        <div class="post-right">
          <a class="button is-light"><i class="fas fa-share-alt"></i></a>
          <a class="button is-light" @click="likeToggle" v-bind:class="{ 'is-danger': isLiked }">
            <i class="fas fa-heart"></i>
            <span class="likenum">{{ likeNum }}</span>
          </a>
        </div>
        <p class="is-size-7 has-text-grey">{{ photo.p_created_at }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import DetailContent from "./DetailContent.vue";

export default {
  components: {
    "detail-content": DetailContent
  },
  name: "post-component",
  props: ["photo"],
  data: function() {
    return {
      showModal: false,
      isLiked: false,
      likeNum: 0,
      touchOffset: 0,
      startModal: 0,
      startModalBar: 0
    };
  },
  async created() {
    try {
      let res;
      res = await axios.get(
        "/api/photos/like/check/" + user_screen_name + "/" + this.photo.p_id
      );
      if (res.data == true) {
        this.isLiked = true;
      }
    } catch (e) {
      console.error(e);
    }

    this.likeNum = this.photo.likes;
  },
  methods: {
    likeToggle: function() {
      axios
        .post("/api/photos/like/toggle", {
          screen_name: user_screen_name,
          api_token: user_api_token,
          photo_id: this.photo.p_id,
          csrfToken: window.Laravel.csrfToken
        })
        .then(response => {
          this.likeToggleData();
        })
        .catch(error => {
          console.log(error.response);
        });
    },
    likeToggleData: function() {
      if (this.isLiked == true) {
        this.isLiked = false;
        this.likeNum = this.likeNum - 1;
      } else {
        this.isLiked = true;
        this.likeNum = this.likeNum + 1;
      }
    },
    modalToggle: function() {
      if (this.showModal) {
        window.history.back();
      } else {
        history.pushState("", "", "/photo/" + this.photo.p_id);
        window.scrollTo(0, 0);
      }
      this.showModal = !this.showModal;
    },
    t_start(event) {
      const touchObject = event.changedTouches[0];
      this.touchOffset = touchObject.screenY;
      this.startModal = this.$refs.modal.style.top;
      this.startModalBar = this.$refs.modalbar.style.top;
    },
    t_move(event) {
      const touchObject = event.changedTouches[0];
      const offset = touchObject.screenY - this.touchOffset;
      this.$refs.modal.style.top = offset + "px";
      this.$refs.modalbar.style.top = offset + "px";
      this.$refs.modal.style.opacity =
        1.0 - offset / window.parent.screen.height;
    },
    t_end(event) {
      const touchObject = event.changedTouches[0];
      const offset = touchObject.screenY - this.touchOffset;
      if (offset >= window.parent.screen.height / 3) {
        this.modalToggle();
      } else {
        this.$refs.modal.style.top = this.startModal;
        this.$refs.modalbar.style.top = this.startModalBar;
        this.$refs.modal.style.opacity = 1.0;
      }
    }
  }
};
</script>
<style scoped lang="scss">
.slidemark {
  height: 0.5rem;
  width: 3rem;
  margin-top: 0.25rem;
  border-radius: 0.25rem;
  display: inline-block;
}
.scrollarea {
  overflow-y: scroll;
  position: absolute;
  top: 3rem;
  bottom: 0;
  left: 0;
  right: 0;
}
.slidearea {
  margin-top: 2.5px;
  height: 3rem;
  line-height: 3rem;
  border-radius: 2rem 2rem 0px 0px;
  text-align: center;
  cursor: pointer;
  z-index: 100000;
  position: fixed;
  top: 58px;
  left: 0;
  right: 0;
  @media (max-width: 800px) {
    top: 0.1rem;
  }
}
.likenum {
  margin-left: 0.5rem;
}
.post {
  padding-top: 1rem;
  padding-bottom: 1rem;
  min-height: 100%;
  .post-header {
    height: 2rem;
    position: relative;
    margin-bottom: 0.2em;

    .post-header-left {
      display: inline-block;
      position: absolute;
      left: 0.5rem;
      top: 0;
      bottom: 0;
      p {
        display: inline-block;
        vertical-align: middle;
        height: 2rem;
        line-height: 1rem;
        overflow: hidden;
      }
      p img {
        width: 2rem;
        height: 2rem;
        margin-right: 0.2rem;
        border-radius: 100%;
      }
    }
    .post-header-right {
      display: inline-block;
      position: absolute;
      right: 0.5rem;
      top: 0;
      bottom: 0;
    }
  }
  .post-contents {
    margin-top: 0.25rem;
    background-color: #101010;
    text-align: center;
    img {
      display: inline-block;
      max-width: 800px;
      max-height: 80vh;
    }
    @media (max-width: 800px) {
      img {
        display: block;
        width: 100%;
        max-width: none;
      }
    }
  }
  .post-footer {
    position: relative;
    margin-left: 0.5rem;
    .post-right {
      display: inline-block;
      position: absolute;
      right: 0.5rem;
      top: 0.2rem;
      bottom: 0;
    }
    margin-top: 0rem;
  }
}

.modal {
  z-index: 1000;
  position: fixed;
  top: 56px;
  bottom: 0;
  left: 0;
  right: 0;
  display: block;
}
.closeBtn {
  position: fixed;
  top: 60px;
  right: 3px;
  color: white;
  padding: 1rem;
  z-index: 100000;
}
@media (max-width: 800px) {
  .modal {
    top: 0;
    bottom: 45px;
  }
  .closeBtn {
    top: 3px;
  }
}

a strong {
  color: #000000;
}

a {
  color: white;
}

.is-size-8 {
  font-size: 75%;
}
</style>
