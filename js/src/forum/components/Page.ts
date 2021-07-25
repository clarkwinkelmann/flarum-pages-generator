import {Vnode} from 'mithril';
import app from 'flarum/forum/app';
import BasePage from 'flarum/common/components/Page';
import PageModel from '../models/Page';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';

export default class Page extends BasePage {
    page: PageModel | null = null

    oninit(vnode: Vnode) {
        super.oninit(vnode);

        const id = (vnode.attrs as any).routeName.substr(16); // Length of the prefix in route definition

        const preloaded = app.preloadedApiDocument();

        if (preloaded instanceof PageModel) {
            this.show(preloaded);
        } else {
            app.request({
                method: 'GET',
                url: app.forum.attribute('apiUrl') + '/generated-route',
                params: {
                    path: id,
                },
            }).then(response => {
                this.show(app.store.pushPayload(response));

                m.redraw();
            });
        }
    }

    show(page: PageModel) {
        this.page = page;

        app.setTitle(page.title());
    }

    view() {
        if (!this.page) {
            return LoadingIndicator.component();
        }

        return m('.container', [
            m('h1', this.page.title()),
            this.page.content().map(content => {
                switch (content.type) {
                    case 'html':
                        return m.trust(content.body);
                    case 'mithril':
                        // TODO: make it work without eval so we can use CSP
                        return m(eval(content.component), content.attrs);
                }

                return null;
            }),
        ]);
    }
}
