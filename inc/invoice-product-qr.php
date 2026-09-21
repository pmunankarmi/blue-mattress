<?php
/**
 * Static demo QR on PDF invoices. Encodes only "DEMO", never a URL.
 * This placeholder is not a tax-verification code.
 */
defined( 'ABSPATH' ) || exit;

/** Place one non-clickable demo image in the notes cell beside the totals. */
function blue_invoice_demo_qr( $document_type, $order ): void {
	if ( 'invoice' !== $document_type ) {
		return;
	}
	$image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHEAAABxCAIAAABtHNuHAAAACXBIWXMAAA7EAAAOxAGVKw4bAAACFUlEQVR4nO2cwY6DMAwFl1X//5fZa5WDtyZjY6qZW1WV0JHFUxKT4zzPH0H5vfsGvhCd8uiU5/X+4TiOnlGXh3g8bvzEX36bujLI+7jWKY9OeXTKo1OeV/AdOB3YSaH4Uqnf9vwj65RHpzw65dEpT5RRC6k5Sdty105kFf0j65RHpzw65dEpTyKj6thZkRu492Od8uiUR6c8OuUZkVEp4h2nCZFlnfLolEenPDrlSWRU2+N/p0kCHOgy1imPTnl0yqNTniij2priYlKde6l8K8I65dEpj055dMpzTFgcS3X9gS2CRVinPDrl0SmPTnmi96PAlvCdDjowwVJcXnK0Tnl0yqNTHp3yRPMocJHtn5vg1uvqXvi19/xOdMqjUx6d8lT19e3MZ1KRtRNoqcj6/K6sUx6d8uiUR6c818+ZqIuOGHCWVdRCYZ3y6JRHpzw65Umc1zfz3Ly6uHM/ahA65dEpj055HtDXtwB2Y8S3cXn/zTrl0SmPTnl0yjPi3PPUt+BARZeyTnl0yqNTHp3yPODc87aZUgrX+lrRKY9OeXTKM+Lc851Gh7a+vs+xTnl0yqNTHp3yjDhTti5nUvlGRZZ1yqNTHp3y6JRnREYttB3Qt5N+AdYpj055dMqjU57nnXte935UauLkflQrOuXRKY9OeUace97Wex5fKsa1vjvRKY9OeXTKM+L9qC/DOuXRKY9Oef4AKwUC+fWpTLAAAAAASUVORK5CYII=';
	echo '<div style="margin-top:8px;page-break-inside:avoid;">';
	echo '<img src="' . esc_attr( $image ) . '" alt="Demo QR" style="width:28mm;height:28mm;" />';
	echo '</div>';
}
add_action( 'wpo_wcpdf_before_document_notes', 'blue_invoice_demo_qr', 10, 2 );
