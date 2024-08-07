import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor'
// Plugins - sorted alphabetically by import
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment'
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold'
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic'
import Strikethrough from '@ckeditor/ckeditor5-basic-styles/src/strikethrough'
import Subscript from '@ckeditor/ckeditor5-basic-styles/src/subscript'
import Superscript from '@ckeditor/ckeditor5-basic-styles/src/superscript'
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline'
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote'
import Clipboard from '@ckeditor/ckeditor5-clipboard/src/clipboard'
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials'
import Font from '@ckeditor/ckeditor5-font/src/font'
import Heading from '@ckeditor/ckeditor5-heading/src/heading'
import HorizontalLine from '@ckeditor/ckeditor5-horizontal-line/src/horizontalline'
import Image from '@ckeditor/ckeditor5-image/src/image'
import ImageCaption from '@ckeditor/ckeditor5-image/src/imagecaption'
import ImageResize from '@ckeditor/ckeditor5-image/src/imageresize'
import ImageStyle from '@ckeditor/ckeditor5-image/src/imagestyle'
import ImageToolbar from '@ckeditor/ckeditor5-image/src/imagetoolbar'
import ImageUpload from '@ckeditor/ckeditor5-image/src/imageupload.js'
import Link from '@ckeditor/ckeditor5-link/src/link'
import List from '@ckeditor/ckeditor5-list/src/list'
import ListProperties from '@ckeditor/ckeditor5-list/src/listproperties'
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph'
import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice'
import Table from '@ckeditor/ckeditor5-table/src/table'
import TableCaption from '@ckeditor/ckeditor5-table/src/tablecaption'
import TableCellProperties from '@ckeditor/ckeditor5-table/src/tablecellproperties'
import TableColumnResize from '@ckeditor/ckeditor5-table/src/tablecolumnresize'
import TableProperties from '@ckeditor/ckeditor5-table/src/tableproperties'
import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar'
// custom plugins
import CKEditorPrintPlugin from 'Components/CKEditor/CustomPlugins/CKEditorPrintPlugin/CKEditorPrintPlugin'
import CustomBase64UploadAdapter from 'Components/CKEditor/CustomAdapter/CKEditorBase64UploadAdapter'

export default class CustomCKEditor extends ClassicEditor {}

// eslint-disable-next-line functional/immutable-data
ClassicEditor.builtinPlugins = [
    Alignment,
    BlockQuote,
    Bold,
    Clipboard,
    Essentials,
    Font,
    Heading,
    HorizontalLine,
    Image,
    ImageCaption,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Italic,
    Link,
    List,
    ListProperties,
    Paragraph,
    PasteFromOffice,
    Strikethrough,
    Subscript,
    Superscript,
    Table,
    TableCaption,
    TableCellProperties,
    TableColumnResize,
    TableProperties,
    TableToolbar,
    Underline,
    CKEditorPrintPlugin,
    CustomBase64UploadAdapter,
]

// eslint-disable-next-line functional/immutable-data
ClassicEditor.defaultConfig = {
    toolbar: {
        items: [
            'heading',
            '|',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'subscript',
            'superscript',
            'blockQuote',
            'alignment',
            '|',
            'fontSize',
            'fontFamily',
            'fontColor',
            'fontBackgroundColor',
            '|',
            'bulletedList',
            'numberedList',
            'insertTable',
            '|',
            'link',
            'imageUpload',
            'horizontalLine',
            '|',
            'undo',
            'redo',
            '|',
            'print',
        ],
    },
    // @ts-ignore
    table: {
        contentToolbar: [
            'toggleTableCaption',
            '|',
            'tableRow',
            'tableColumn',
            '|',
            'tableProperties',
            'tableCellProperties',
            '|',
            'mergeTableCells',
        ],
    },
    // @ts-ignore
    list: {
        properties: {
            styles: true,
            startIndex: true,
            reversed: true,
        },
    },
    // @ts-ignore
    image: {
        toolbar: [
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative',
        ],
        upload: {
            types: ['jpeg', 'gif', 'webp'],
        },
    },
    language: 'de',
}
