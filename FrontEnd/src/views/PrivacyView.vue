<!-- FrontEnd/src/views/PrivacyView.vue -->
<template>
  <div class="privacy-page">
    <RouterLink to="/" class="page-back" :aria-label="$t('notFound.homeBtn')">←</RouterLink>
    <article class="privacy-card">
      <h1>{{ doc.title }}</h1>
      <p class="updated">{{ doc.updated }}</p>
      <p>{{ doc.intro }}</p>

      <section v-for="section in doc.sections" :key="section.title">
        <h2>{{ section.title }}</h2>
        <p v-if="section.text">{{ section.text }}</p>
        <ul v-if="section.items">
          <li v-for="item in section.items" :key="item">{{ item }}</li>
        </ul>
      </section>

      <p class="contact">
        {{ doc.contact }} <a :href="`mailto:${CONTACT_EMAIL}`">{{ CONTACT_EMAIL }}</a>
      </p>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

// Linked from the Google OAuth consent screen (Branding → privacy policy
// link), so keep it public and keep it in step with what the app actually
// stores: users / recycling_sessions / transactions / points_history /
// reward_redemptions / detection_logs.
const CONTACT_EMAIL = 'lutpiy03@gmail.com'

const content = {
  en: {
    title: 'Privacy Policy',
    updated: 'Last updated: 28 October 2026',
    intro: 'This policy explains what the RVM (Reverse Vending Machine) smart recycling web app and machines collect about you, why, and how you can delete it.',
    sections: [
      {
        title: '1. What we collect',
        items: [
          'Account details: your name, email address, an optional phone number, and your password (stored only as a one-way hash, never in plain text).',
          'Google Sign-In (only if you choose it): your Google name, email address, profile picture and Google account ID. We never receive your Google password or any other Google data.',
          'Recycling activity: your recycling sessions, the items you deposit (material type, the AI detection result and its confidence, weight), the machine used, the time, and the points you earn and spend.',
          'Item photos: the machine camera photographs each deposited item so the AI can identify its material. The photos are of the item, and are kept to check and improve detection accuracy.',
          'Rewards: the rewards you redeem, their voucher codes and when a voucher was used. For a cash redemption, also the e-wallet provider and account number you enter.',
          'Preferences: your language and light/dark theme.',
        ],
      },
      {
        title: '2. How we use it',
        items: [
          'To create your account and sign you in.',
          'To record what you recycle and give you points.',
          'To process rewards and vouchers, and let a partner check that a voucher is valid.',
          'To check and improve how accurately the machine identifies materials.',
          'To run and maintain the machines, and to produce overall statistics (for example, how much was recycled and the estimated carbon saved).',
        ],
      },
      {
        title: '3. Who can see it',
        items: [
          'We do not sell your data or use it for advertising.',
          'RVM administrators can see account and activity data in order to run the service.',
          'Anyone holding a voucher code or its QR code can open the voucher check page, which shows only the reward, the redemption date, whether it has been used, and a shortened form of your name (for example "Adi L***").',
          'The service runs on these providers, who store or process the data on our behalf: Vercel (website), Render (server), TiDB Cloud (database) and, if you use it, Google (sign-in).',
        ],
      },
      {
        title: '4. Data stored on your device',
        text: 'The website keeps your sign-in token, language and theme in your browser\'s local storage so you stay signed in and see your settings. We do not use advertising or tracking cookies.',
      },
      {
        title: '5. Keeping and deleting your data',
        text: 'Your data is kept for as long as your account exists. You can delete your account at any time in Settings → Danger Zone → Delete Account. This permanently removes your account together with its recycling sessions, deposits, points history and reward redemptions. Item photos are kept for detection accuracy, but are no longer linked to you.',
      },
      {
        title: '6. Changes to this policy',
        text: 'If this policy changes, we will update this page and the date at the top.',
      },
    ],
    contact: 'Questions or requests about your data:',
  },
  my: {
    title: 'Dasar Privasi',
    updated: 'Kemas kini terakhir: 28 Oktober 2026',
    intro: 'Dasar ini menerangkan maklumat yang dikumpul oleh aplikasi web dan mesin kitar semula pintar RVM (Reverse Vending Machine) tentang anda, sebabnya, dan cara anda boleh memadamkannya.',
    sections: [
      {
        title: '1. Maklumat yang kami kumpul',
        items: [
          'Butiran akaun: nama, alamat e-mel, nombor telefon (pilihan), dan kata laluan anda (disimpan hanya dalam bentuk cincang sehala, tidak sekali-kali sebagai teks biasa).',
          'Log masuk Google (hanya jika anda memilihnya): nama, alamat e-mel, gambar profil dan ID akaun Google anda. Kami tidak menerima kata laluan Google atau data Google anda yang lain.',
          'Aktiviti kitar semula: sesi kitar semula, item yang anda masukkan (jenis bahan, keputusan pengesanan AI dan tahap keyakinannya, berat), mesin yang digunakan, masa, serta mata yang anda peroleh dan belanjakan.',
          'Foto item: kamera mesin mengambil foto setiap item yang dimasukkan supaya AI dapat mengenal pasti bahannya. Foto ini ialah foto item, dan disimpan untuk menyemak serta meningkatkan ketepatan pengesanan.',
          'Ganjaran: ganjaran yang anda tebus, kod baucarnya dan bila baucar itu digunakan. Bagi penebusan tunai, juga penyedia e-dompet dan nombor akaun yang anda masukkan.',
          'Pilihan: bahasa dan tema terang/gelap anda.',
        ],
      },
      {
        title: '2. Cara kami menggunakannya',
        items: [
          'Untuk membuat akaun anda dan melog masuk anda.',
          'Untuk merekod apa yang anda kitar semula dan memberi anda mata.',
          'Untuk memproses ganjaran dan baucar, serta membolehkan rakan kongsi menyemak bahawa baucar itu sah.',
          'Untuk menyemak dan meningkatkan ketepatan mesin mengenal pasti bahan.',
          'Untuk mengendalikan dan menyelenggara mesin, serta menghasilkan statistik keseluruhan (contohnya jumlah yang dikitar semula dan anggaran karbon yang dijimatkan).',
        ],
      },
      {
        title: '3. Siapa yang boleh melihatnya',
        items: [
          'Kami tidak menjual data anda atau menggunakannya untuk pengiklanan.',
          'Pentadbir RVM boleh melihat data akaun dan aktiviti untuk mengendalikan perkhidmatan.',
          'Sesiapa yang memegang kod baucar atau kod QR-nya boleh membuka halaman semakan baucar, yang hanya menunjukkan ganjaran, tarikh penebusan, sama ada ia telah digunakan, dan nama anda dalam bentuk ringkas (contohnya "Adi L***").',
          'Perkhidmatan ini berjalan di atas penyedia berikut, yang menyimpan atau memproses data bagi pihak kami: Vercel (laman web), Render (pelayan), TiDB Cloud (pangkalan data) dan, jika anda menggunakannya, Google (log masuk).',
        ],
      },
      {
        title: '4. Data yang disimpan pada peranti anda',
        text: 'Laman web menyimpan token log masuk, bahasa dan tema anda dalam storan setempat pelayar supaya anda kekal log masuk dan melihat tetapan anda. Kami tidak menggunakan kuki pengiklanan atau penjejakan.',
      },
      {
        title: '5. Penyimpanan dan pemadaman data',
        text: 'Data anda disimpan selagi akaun anda wujud. Anda boleh memadamkan akaun anda pada bila-bila masa di Tetapan → Zon Bahaya → Padam Akaun. Ini memadamkan akaun anda secara kekal bersama sesi kitar semula, item yang dimasukkan, sejarah mata dan penebusan ganjarannya. Foto item disimpan untuk ketepatan pengesanan, tetapi tidak lagi dikaitkan dengan anda.',
      },
      {
        title: '6. Perubahan pada dasar ini',
        text: 'Jika dasar ini berubah, kami akan mengemas kini halaman ini dan tarikh di bahagian atas.',
      },
    ],
    contact: 'Soalan atau permintaan berkaitan data anda:',
  },
}

const { locale } = useI18n()
const doc = computed(() => content[locale.value] || content.en)
</script>

<style scoped>
.privacy-page { min-height: 100dvh; padding: 72px 16px 40px; }
.page-back {
  position: fixed; top: 16px; left: 16px; z-index: 10;
  width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
  background: var(--bg-card); color: var(--text-primary);
  border: 1px solid var(--border); border-radius: 12px;
  box-shadow: var(--shadow); text-decoration: none; font-size: 20px;
}
.privacy-card {
  max-width: 720px; margin: 0 auto; padding: 28px 24px;
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  color: var(--text-secondary); font-size: 14px; line-height: 1.65;
}
h1 { font-size: 24px; color: var(--text-primary); }
.updated { font-size: 12px; color: var(--text-muted); margin: 4px 0 16px; }
section { margin-top: 20px; }
h2 { font-size: 16px; color: var(--text-primary); margin-bottom: 6px; }
ul { padding-left: 20px; display: flex; flex-direction: column; gap: 6px; }
.contact { margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); }
.contact a { color: var(--accent-blue); }
</style>
