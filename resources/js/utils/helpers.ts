export const formulateURL = (filters:any,sorting: any,pagination:any)=>{
    const params = new URLSearchParams();
    // Apply filtering
    Object.entries(filters).forEach(([key, value]) => {
        if (value) params.append(`filter[${key}]`, value);
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

