import type { CapacitorConfig } from '@capacitor/cli'

const config: CapacitorConfig = {
  appId: 'xyz.enoki.capacitor.template',
  appName: 'CapacitorTemplate',
  webDir: 'dist',
  plugins: {
    StatusBar: {
      overlaysWebView: false,
    },
    // BackgroundRunner: {
    //   label: 'xyz.enoki.capacitor.template.background',
    //   src: 'runners/background-runner.js',
    //   event: 'CapacitorTemplateBackgroundRunner',
    //   repeat: true,
    //   /** OSの制約で15分間隔 */
    //   interval: 15,
    //   autoStart: true,
    // },
    PushNotifications: {
      presentationOptions: ['badge', 'sound', 'alert'],
    },
    CapacitorHttp: {
      enabled: true,
    },
  },
  server: {
    hostname: 'capacitor-template.enoki.xyz',
    androidScheme: 'https',
  },
  // deepLinks: {
  //   enabled: true,
  //   prefixes: ['capacitor://', 'https://capacitor-template.enoki.xyz'],
  // },
  android: {
    // useLegacyBridge: true,
    // notificationIcon: 'ic_location_status',
  },
}

export default config
