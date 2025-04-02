export const formulateURL = (filters:any,sorting: any,pagination:any)=>{
    const params = new URLSearchParams();
    // Apply filtering
    filters.forEach((filter: any) => {
        if (filter.value) params.append(`filter[${filter.urlName}]`, filter.value);
    });

    // Apply sorting
    if (sorting.length > 0) {
        const sortString = sorting
            .map((s: SortingValue) => (s.desc ? `-${s.id}` : s.id)) // Convert sorting object to query format
            .join(',');
        params.append('sort', sortString);
    }

    // Apply pagination
    if (pagination.pageIndex > 0)
    params.append('page', pagination.pageIndex + 1);

    return params;
}
export const extractSorting = (params: URLSearchParams) => {
    const sortParams = params.get('sort');
    let sorting : SortingValue[] = [];
    if (sortParams) {
        sorting = sortParams.split(',').map(sort => {
            return {
                id: sort.replace('-', ''), // Remove '-' to get the column name
                desc: sort.startsWith('-'), // If '-' is present, sort descending
                urlName: sort.replace('-', '') // Same as column name for URL handling
            };
        });
    }
    return sorting
}

