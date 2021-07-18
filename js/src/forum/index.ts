import app from 'flarum/forum/app';
import Page from './components/Page';
import PageModel from './models/Page';

app.initializers.add('pages-generator', () => {
    app.store.models['generator-page'] = PageModel;

    app.data.generatorPages.forEach(path => {
        app.routes['generated-route.' + path] = {
            path,
            component: Page,
        };
    });
});
