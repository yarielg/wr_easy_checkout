<template>

  <el-col class="container-fluid wrech-admin-main p-0 m-0" v-loading="loading">
    <el-row class="tac">
      <el-col :span="6" class="wrech-sidebar">
        <el-menu
                default-active="2"
                class="el-menu-vertical-main"
                @open="handleOpen"
                @close="handleClose"
        >
          <!--<el-sub-menu index="1">
            <template #title>
              <el-icon><list /></el-icon>
              <span>General</span>
            </template>
            <el-menu-item-group title="Group One">
              <el-menu-item index="1-1">item one</el-menu-item>
              <el-menu-item index="1-2">item one</el-menu-item>
            </el-menu-item-group>
            <el-menu-item-group title="Group Two">
              <el-menu-item index="1-3">item three</el-menu-item>
            </el-menu-item-group>
            <el-sub-menu index="1-4">
              <template #title>item four</template>
              <el-menu-item index="1-4-1">item one</el-menu-item>
            </el-sub-menu>
          </el-sub-menu>-->
          <el-menu-item index="2" @click="changeScreen('cart')">
            <el-icon><shopping-cart /></el-icon>
            <span>Cart Styles</span>
          </el-menu-item>
          <el-menu-item index="3" @click="changeScreen('settings')">
            <el-icon><setting /></el-icon>
            <span>Settings</span>
          </el-menu-item>
          <el-menu-item index="4" disabled>
            <el-icon><help /></el-icon>
            <span>License and Support</span>
          </el-menu-item>
        </el-menu>
      </el-col>
      <el-col :span="18" class="main-area">

       <div class="cart" v-show="active_screen === 'cart'">
         <p class="wrech-label"><strong>Cart Float Button Position</strong>:</p>
         <el-form-item>
           <el-radio-group v-model="float_btn_position">
             <el-radio-button class="btn_position" label="bottom_left" ><img class="position_image" :src="plugin_url + '/assets/images/bottom_left.png'" alt=""></el-radio-button>
             <el-radio-button class="btn_position" label="bottom_right" ><img class="position_image" :src="plugin_url + '/assets/images/bottom_right.png'" alt=""></el-radio-button>
             <el-radio-button class="btn_position" label="up_right" ><img class="position_image" :src="plugin_url + '/assets/images/up_right.png'" alt=""></el-radio-button>
             <el-radio-button class="btn_position" label="up_left"><img class="position_image" :src="plugin_url + '/assets/images/up_left.png'" alt=""></el-radio-button>
           </el-radio-group>
         </el-form-item>

         <br>
         <p class="wrech-label"><strong>Cart Icon:</strong></p>
         <el-upload
                 ref="upload"
                 :action="ajax_url"
                 :data="file_data"
                 list-type="picture-card"
                 accept="image/png, image/jpeg"
                 :file-list="uploaded_files"
                 :limit="1"
                 :on-exceed="handleExceed"
                 :on-success="iconUploaded"
                 :on-progress="iconUploading"
                 :on-error="iconUploadError"
                 :on-change="fileChanged"
                 :on-remove="defaultIcon"
                 class="wrech_single_uploader"
         >
           <el-icon><plus /></el-icon>
         </el-upload>
       </div>
        <div class="settings" v-show="active_screen === 'settings'">
          <p class="wrech-label"><strong>Excluded Pages:</strong></p>

          <el-select v-model="excluded_pages"  multiple placeholder="Select Pages" size="large" class="wrech-select">
            <el-option
                    v-for="page in pages"
                    :key="page.ID"
                    :label="page.post_title + ' ID: ' +page.ID"
                    :value="page.ID"
            >
            </el-option>
          </el-select>
          <p class="wrech-field-description mt-3">List pages where the cart modal won't show, Cart and checkout page are excluded.</p>
        </div>

        <br>
        <el-button type="primary" @click="saveCustomization()" round>Save Settings <el-icon class="el-icon--right"> <check /></el-icon></el-button>
    </el-col>
  </el-row>
  </el-col>
</template>

<script>
  const axios = require('axios');
  import { Check, Plus,Setting,ShoppingCart, Help, List } from '@element-plus/icons-vue';
  import { ElMessage } from 'element-plus';

  export default {
    components:{
      Check,Plus,Setting, ShoppingCart, Help, List
    },
    data() {
      return {
        loading: false,
        float_btn_position: wrech_settings_params.settings.float_btn_position,
        cart_icon: null,
        uploaded_files: [{
          url: wrech_settings_params.settings.cart_icon_url
        }],
        upload_dirty: false,
        file_data: {
          action: 'wrech_add_cart_icon'
        },
        plugin_url: wrech_settings_params.plugin_url,
        ajax_url: wrech_settings_params.ajax_url,
        active_screen: 'cart',
        pages: [],
        excluded_pages: []
      }
    },
    computed:{

    },
    created(){
      this.getPages();
    },
    methods:{
      changeScreen(screen){
        this.active_screen = screen;
        console.log(screen)
      },
      fileChanged(file){
        // this.$refs.upload.submit();
      },
      handleExceed(files){
        this.$refs.upload.clearFiles()
        this.$refs.upload.handleStart(files[0])
        this.$refs.upload.submit()
      },
      iconUploadError(err){
        console.log(err);
        this.loading = false;
      },
      iconUploading(){
        this.loading = true;
      },
      iconUploaded(response,file, fileList){
        console.log(response)
        this.loading = false;
      },
      defaultIcon(){
        const formData = new FormData();
        formData.append('action', 'wrech_default_icon');
        this.loading = true;
        axios.post(wrech_settings_params.ajax_url, formData)
                .then( response => {
                  if(response.data.success){
                    ElMessage.success('Cart Icon Defaulted!')
                  }else{
                    ElMessage.error(response.data.msg)
                  }

                });
        this.loading = false;
      },
      saveCustomization(){
        const formData = new FormData();
        formData.append('action', 'wrech_save_customizations');
        formData.append('float_btn_position', this.float_btn_position);
        formData.append('excluded_pages', this.excluded_pages);

        this.loading = true;
        axios.post(wrech_settings_params.ajax_url, formData)
            .then( response => {
              if(response.data.success){
                ElMessage.success('Settings Saved!')
              }else{
                ElMessage.error(response.data.msg)
              }
              this.loading = false;
        });
      },
      getPages(){
        const formData = new FormData();
        formData.append('action', 'wrech_get_pages');
        this.loading = true;
        axios.post(wrech_settings_params.ajax_url, formData)
            .then( response => {
              if(response.data.success){
                this.pages = response.data.pages;
                this.excluded_pages = response.data.excluded_pages;
              }else{
                ElMessage.error(response.data.msg)
              }
        });
        this.loading = false;
      }
    }
  }
</script>

<style>
  #wrech-app{
    background: #eef1f5 !important;
    height: 100vh;
  }

  .toplevel_page_wrech-test .notice{
    display: none !important;
  }

  .wrech-sidebar .panel-wrapper{
    height: 100vh;
  }

  .box-panel .panel-wrapper {
    background: white;
    border-radius: 8px;
    color: #78849c;
    padding: 50px 20px;
  }

  .position_image{
    width: 60px;
  }

  .btn_position{
    margin-right: 50px;
  }

  .btn_position .el-radio-button__inner{
    padding: 5px;
  }

  .btn_position[aria-checked='true']{
    border-radius: 5px;
    -webkit-box-shadow: 1px 2px 8px 0px rgba(0,0,0,0.62);
    box-shadow: 1px 2px 8px 0px rgba(0,0,0,0.62);
  }

  .wrech_single_uploader .el-upload--picture-card, .wrech_single_uploader .el-upload-list--picture-card .el-upload-list__item{
    width: 60px !important;
    height: 60px !important;
  }

  .wrech_single_uploader .el-upload--picture-card i{
    margin-top: 20px !important;
    font-size: 18px !important;
  }

  .wrech_single_uploader .el-upload-list__item-preview{
    display: none !important;
  }

  .wrech_single_uploader .el-upload-list--picture-card .el-upload-list__item-actions span+span{
    margin-left: 0 !important;
  }

  .el-menu-vertical-main{
    height: 100vh !important;
  }

  .main-area{
    padding: 20px !important;
  }

  .wrech-select{
    width: 400px;
  }

</style>
