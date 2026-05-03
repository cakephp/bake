import baseConfig from '@cakephp/docs-skeleton/config'
import { createRequire } from 'module'

const require = createRequire(import.meta.url)
const tocEn = require('./toc_en.json')
const tocEs = require('./toc_es.json')
const tocFr = require('./toc_fr.json')
const tocJa = require('./toc_ja.json')
const tocPt = require('./toc_pt.json')
const tocRu = require('./toc_ru.json')

const versions = {
  text: '3.x',
  items: [
    { text: '3.x (current)', link: 'https://book.cakephp.org/bake/3/', target: '_self' },
    { text: '2.x', link: 'https://book.cakephp.org/bake/2.x/', target: '_self' },
    { text: '1.x', link: 'https://book.cakephp.org/bake/1.x/', target: '_self' },
  ],
}

export default {
  extends: baseConfig,
  srcDir: '.',
  title: 'Bake',
  description: 'CakePHP Bake Documentation',
  base: '/bake/3/',
  rewrites: {
    'en/:slug*': ':slug*',
  },
  sitemap: {
    hostname: 'https://book.cakephp.org/bake/3/',
  },
  themeConfig: {
    siteTitle: false,
    pluginName: "Bake",
    socialLinks: [
      { icon: 'github', link: 'https://github.com/cakephp/bake' },
    ],
    editLink: {
      pattern: 'https://github.com/cakephp/bake/edit/3.x/docs/:path',
      text: 'Edit this page on GitHub',
    },
    sidebar: tocEn,
    nav: [
      { text: 'CakePHP', link: 'https://cakephp.org' },
      { text: 'API', link: 'https://api.cakephp.org/bake/' },
      { ...versions },
    ],
  },
  locales: {
    root: {
      label: 'English',
      lang: 'en',
      themeConfig: {
        sidebar: tocEn,
      },
    },
    es: {
      label: 'Español',
      lang: 'es',
      themeConfig: {
        sidebar: tocEs,
      },
    },
    fr: {
      label: 'Français',
      lang: 'fr',
      themeConfig: {
        sidebar: tocFr,
      },
    },
    ja: {
      label: '日本語',
      lang: 'ja',
      themeConfig: {
        sidebar: tocJa,
      },
    },
    pt: {
      label: 'Português',
      lang: 'pt',
      themeConfig: {
        sidebar: tocPt,
      },
    },
    ru: {
      label: 'Русский',
      lang: 'ru',
      themeConfig: {
        sidebar: tocRu,
      },
    },
  },
}
