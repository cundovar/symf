import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';

export default class extends Controller {
    connect() {
        new Splide(this.element).mount();
    }
}
