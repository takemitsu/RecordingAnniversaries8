import '@testing-library/jest-dom'
import { vi } from 'vitest'

// Inertia.js のモック（テスト環境でのみ上書き）
declare global {
  var route: ReturnType<typeof vi.fn>
}

global.route = vi.fn(() => '#')

// Window.confirm のモック（useConfirmDelete で使用）
Object.defineProperty(window, 'confirm', {
  writable: true,
  value: vi.fn(() => true),
})