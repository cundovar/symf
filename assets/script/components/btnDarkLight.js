

document.addEventListener('turbo:load', () => {


    
    console.log('btnDarkLight')
const btn= document.querySelector('.btnTheme')

btn.addEventListener('mouseenter', () => {
    btn.classList.add('btnThemehovered')
    console.log('entrée')
})

btn.addEventListener('mouseleave', () => {
    btn.classList.remove('btnThemehovered')
    console.log('sortie')
})

})