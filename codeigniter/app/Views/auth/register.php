<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <img class="mx-auto h-16 w-auto" src="<?= base_url('assets/images/logo.png') ?>" alt="หน่วยงานภาครัฐทางด้านกฎหมาย">
    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
      ลงทะเบียน
    </h2>
    <p class="mt-2 text-center text-sm text-gray-600">
      สร้างบัญชีใหม่สำหรับใช้งานระบบ
    </p>
  </div>

  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">

      <?php if (session()->getFlashdata('error')) : ?>
        <div class="rounded-md bg-red-50 p-4 mb-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800"><?= session()->getFlashdata('error') ?></h3>
              <?php if (session()->getFlashdata('validation')) : ?>
                <div class="mt-2 text-sm text-red-700">
                  <ul class="list-disc pl-5 space-y-1">
                    <?php foreach (session()->getFlashdata('validation')->getErrors() as $error) : ?>
                      <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <form class="space-y-6" action="<?= site_url('register') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Profile Image Upload -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            รูปโปรไฟล์
          </label>
          <div class="mt-1 flex flex-col items-center">
            <!-- Preview -->
            <div class="mb-3" id="imagePreviewContainer">
              <img src="<?= base_url('uploads/profiles/default.png') ?>"
                alt="รูปโปรไฟล์"
                class="h-32 w-32 rounded-full object-cover border-2 border-gray-200"
                id="imagePreview">
            </div>

            <!-- Drag & Drop Area -->
            <div class="w-full flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
              id="dropArea">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-600">
                  <label for="profile_image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                    <span>อัปโหลดไฟล์</span>
                    <input id="profile_image" name="profile_image" type="file" class="sr-only" accept="image/*">
                  </label>
                  <p class="pl-1">หรือลากและวางที่นี่</p>
                </div>
                <p class="text-xs text-gray-500">
                  PNG, JPG, GIF ขนาดไม่เกิน 2MB
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- ชื่อผู้ใช้และอีเมล -->
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700">
              ชื่อผู้ใช้
            </label>
            <div class="mt-1">
              <input id="username" name="username" type="text" autocomplete="username" required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                                shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                                focus:border-blue-500 sm:text-sm" value="<?= old('username') ?>">
            </div>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              อีเมล
            </label>
            <div class="mt-1">
              <input id="email" name="email" type="email" autocomplete="email" required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                                shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                                focus:border-blue-500 sm:text-sm" value="<?= old('email') ?>">
            </div>
          </div>
        </div>

        <!-- รหัสผ่าน -->
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              รหัสผ่าน
            </label>
            <div class="mt-1">
              <input id="password" name="password" type="password" autocomplete="new-password" required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                                shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                                focus:border-blue-500 sm:text-sm">
            </div>
            <p class="mt-1 text-xs text-gray-500">รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร</p>
          </div>

          <div>
            <label for="password_confirm" class="block text-sm font-medium text-gray-700">
              ยืนยันรหัสผ่าน
            </label>
            <div class="mt-1">
              <input id="password_confirm" name="password_confirm" type="password"
                autocomplete="new-password" required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                                shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                                focus:border-blue-500 sm:text-sm">
            </div>
          </div>
        </div>

        <!-- ข้อมูลส่วนตัว -->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700">
            คำนำหน้าชื่อ
          </label>
          <div class="mt-1">
            <select id="title" name="title"
              class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md 
                            shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              <option value="">-- เลือก --</option>
              <option value="นาย" <?= old('title') == 'นาย' ? 'selected' : '' ?>>นาย</option>
              <option value="นาง" <?= old('title') == 'นาง' ? 'selected' : '' ?>>นาง</option>
              <option value="นางสาว" <?= old('title') == 'นางสาว' ? 'selected' : '' ?>>นางสาว</option>
            </select>
          </div>
        </div>

        <div>
          <label for="fullname" class="block text-sm font-medium text-gray-700">
            ชื่อ-นามสกุล
          </label>
          <div class="mt-1">
            <input id="fullname" name="fullname" type="text" required
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                            shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                            focus:border-blue-500 sm:text-sm" value="<?= old('fullname') ?>">
          </div>
        </div>

        <div>
          <label for="department" class="block text-sm font-medium text-gray-700">
            ชื่อกลุ่มงาน
          </label>
          <div class="mt-1">
            <input id="department" name="department" type="text"
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                            shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                            focus:border-blue-500 sm:text-sm" value="<?= old('department') ?>">
          </div>
        </div>

        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
          <div>
            <label for="position" class="block text-sm font-medium text-gray-700">
              ชื่อตำแหน่งงานราชการ
            </label>
            <div class="mt-1">
              <input id="position" name="position" type="text"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                                shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                                focus:border-blue-500 sm:text-sm" value="<?= old('position') ?>">
            </div>
          </div>

          <div>
            <label for="position_level" class="block text-sm font-medium text-gray-700">
              ระดับตำแหน่ง
            </label>
            <div class="mt-1">
              <input id="position_level" name="position_level" type="text"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                                shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                                focus:border-blue-500 sm:text-sm" value="<?= old('position_level') ?>">
            </div>
          </div>
        </div>

        <div>
          <button type="submit"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md 
                        shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 
                        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            ลงทะเบียน
          </button>
        </div>
      </form>

      <div class="mt-6">
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">
              หรือ
            </span>
          </div>
        </div>

        <div class="mt-6">
          <div class="text-center">
            <p class="text-sm text-gray-600">มีบัญชีอยู่แล้ว?</p>
            <a href="<?= site_url('login') ?>" class="mt-2 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              เข้าสู่ระบบ
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- เพิ่มส่วนแสดงข้อมูลเซิร์ฟเวอร์ -->
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="text-center text-xs text-gray-500">
      <p>ระบบปลอดภัยด้วยการเข้ารหัส</p>
      <p>&copy; <?= date('Y') ?> สำนักงานปลัดกระทรวงสาธารณสุข. สงวนลิขสิทธิ์.</p>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('profile_image');
    const imagePreview = document.getElementById('imagePreview');

    // เพิ่ม event listeners สำหรับ drag & drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
      dropArea.classList.add('bg-gray-100');
      dropArea.classList.add('border-blue-500');
    }

    function unhighlight() {
      dropArea.classList.remove('bg-gray-100');
      dropArea.classList.remove('border-blue-500');
    }

    // จัดการไฟล์ที่ลากมาวาง
    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;

      if (files.length > 0) {
        fileInput.files = files;
        updateImagePreview(files[0]);
      }
    }

    // จัดการการอัปโหลดผ่าน file input
    fileInput.addEventListener('change', function() {
      if (this.files.length > 0) {
        updateImagePreview(this.files[0]);
      }
    });

    // แสดงตัวอย่างรูปภาพ
    function updateImagePreview(file) {
      // ตรวจสอบประเภทของไฟล์
      if (!file.type.match('image.*')) {
        alert('กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น');
        return;
      }

      // ตรวจสอบขนาดไฟล์ (ไม่เกิน 2MB)
      if (file.size > 2 * 1024 * 1024) {
        alert('ขนาดไฟล์ต้องไม่เกิน 2MB');
        return;
      }

      const reader = new FileReader();

      reader.onload = function(e) {
        imagePreview.src = e.target.result;
      }

      reader.readAsDataURL(file);
    }
  });
</script>
<?= $this->endSection() ?>