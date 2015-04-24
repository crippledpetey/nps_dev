jQuery(document).ready(function($){
	//set default enabled options
	enableApplicableSeoValues( jQuery("#_nps_seo_category_type option:selected").val() );
});
function enableApplicableSeoValues( type_val ){
	if( type_val == "distinct"){
		jQuery("#_nps_seo_is_primary").prop("disabled", false).parents("tr").removeClass("hidden");

		jQuery("#_nps_seo_is_child").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_parent_id").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_gen_info").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_design_info").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_display_info").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_breadcrumb").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_redirect_type").prop("disabled", true).parents("tr").addClass("hidden");
	}
	if ( type_val == "redirect" ){
		
		jQuery("#_nps_seo_parent_id").prop("disabled", false).parents("tr").removeClass("hidden");
		jQuery("#_nps_seo_redirect_type").prop("disabled", false).parents("tr").removeClass("hidden");

		jQuery("#_nps_seo_is_primary").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_is_child").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_gen_info").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_design_info").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_display_info").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_breadcrumb").prop("disabled", true).parents("tr").addClass("hidden");
					
	}
	if ( type_val == "canonical" ){
		jQuery("#_nps_seo_is_child").prop("disabled", false).parents("tr").removeClass("hidden");
		jQuery("#_nps_seo_parent_id").prop("disabled", false).parents("tr").removeClass("hidden");
		jQuery("#_nps_seo_gen_info").prop("disabled", false).parents("tr").removeClass("hidden");
		jQuery("#_nps_seo_design_info").prop("disabled", false).parents("tr").removeClass("hidden");
		jQuery("#_nps_seo_display_info").prop("disabled", false).parents("tr").removeClass("hidden");
		jQuery("#_nps_seo_breadcrumb").prop("disabled", false).parents("tr").removeClass("hidden");

		jQuery("#_nps_seo_redirect_type").prop("disabled", true).parents("tr").addClass("hidden");
		jQuery("#_nps_seo_is_primary").prop("disabled", true).parents("tr").addClass("hidden");	
	}
}
