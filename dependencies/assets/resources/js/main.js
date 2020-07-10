import initUI from './ui/ui';
import './ui/forms';
import 'flexslider';

window.$ = $;

initUI();

$('.flexslider').flexslider({
    animation: 'slide',
});
