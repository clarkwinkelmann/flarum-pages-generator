import Model from 'flarum/common/Model';

export default class Page extends Model {
    title = Model.attribute('title');
    content = Model.attribute('content');
}
