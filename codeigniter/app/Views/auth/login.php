<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <img class="mx-auto h-16 w-auto" src="<?= base_url('assets/images/logo.png') ?>" alt="หน่วยงานภาครัฐทางด้านกฎหมาย">
    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
      เข้าสู่ระบบ
    </h2>
    <p class="mt-2 text-center text-sm text-gray-600">
      เข้าสู่ระบบบริหารจัดการเว็บแอพพลิเคชัน
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
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('success')) : ?>
        <div class="rounded-md bg-green-50 p-4 mb-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-green-800"><?= session()->getFlashdata('success') ?></h3>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <form class="space-y-6" action="<?= site_url('login') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= $redirect ?? '' ?>">

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
          <label for="password" class="block text-sm font-medium text-gray-700">
            รหัสผ่าน
          </label>
          <div class="mt-1">
            <input id="password" name="password" type="password" autocomplete="current-password" required
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                            shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 
                            focus:border-blue-500 sm:text-sm">
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input id="remember_me" name="remember_me" type="checkbox" value="1"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="remember_me" class="ml-2 block text-sm text-gray-900">
              จำฉันไว้
            </label>
          </div>

          <div class="text-sm">
            <a href="<?= site_url('forgot-password') ?>" class="font-medium text-blue-600 hover:text-blue-500">
              ลืมรหัสผ่าน?
            </a>
          </div>
        </div>

        <div>
          <button type="submit"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md 
                        shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 
                        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            เข้าสู่ระบบ
          </button>
        </div>
      </form>

      <?php if (getenv('ALLOW_REGISTRATION') === 'true'): ?>
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
              <a href="<?= site_url('register') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                ลงทะเบียน
              </a>
            </div>
          </div>
        </div>
      <?php endif; ?>
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
<?= $this->endSection() ?>