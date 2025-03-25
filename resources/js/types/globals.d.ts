import type { route as routeFn } from 'ziggy-js';

export {};
declare global {
    const route: typeof routeFn;
    interface Floor  {
        number: number;
        name: string;
        manager?: {
            name: string;
        };
        roomsCount: number;
        reservedRoomsCount: number;
        availavleRoomsCount: number;
    };
    interface SortingValue {
         id: string;
         desc: boolean
        }

}
